<?php

namespace App\Http\Controllers;

use App\Models\SalesProduct;
use App\Models\SalesTransaction;
use App\Services\InvoiceCalculationService;
use App\Services\SalesCommissionReleaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesTransactionController extends Controller
{
    public function __construct(private InvoiceCalculationService $calc) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        $query = SalesTransaction::with(['salesEmployee', 'product', 'branch'])->latest();

        // Sales hanya lihat transaksi sendiri; admin/superadmin/dst lihat semua (branch-scoped lewat accessibleByUser di model).
        if ($user->hasRole('sales')) {
            abort_unless($employee, 403, 'Akun ini tidak terhubung ke data karyawan.');
            $query->where('sales_employee_id', $employee->id);
        } else {
            $query->accessibleByUser();
        }

        $transactions = $query->paginate(20);

        return view('sales-transactions.index', compact('transactions'));
    }

    public function create(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun ini tidak terhubung ke data karyawan.');

        $profile = $employee->salesNetworkProfile;
        abort_unless($profile?->isActive(), 403, 'Profil jaringan Anda belum aktif.');

        $products = SalesProduct::where('is_active', true)->orderBy('name')->get();

        return view('sales-transactions.create', compact('products'));
    }

    public function store(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun ini tidak terhubung ke data karyawan.');

        $profile = $employee->salesNetworkProfile;
        abort_unless($profile?->isActive(), 403, 'Profil jaringan Anda belum aktif, tidak bisa transaksi.');

        $data = $request->validate([
            'sales_product_id' => ['required', 'exists:sales_products,id'],
            'customer_name'    => ['required', 'string', 'max:255'],
            'customer_phone'   => ['nullable', 'string', 'max:30'],
            'discount'         => ['nullable', 'integer', 'min:0'],
            'downpayment'      => ['nullable', 'integer', 'min:0'],
        ]);

        $product = SalesProduct::findOrFail($data['sales_product_id']);

        // Diskon dibatasi (persen dari harga produk) dan DP tak boleh melebihi
        // total — sales tidak boleh mengatur harga/pembayaran sampai nol.
        $maxDiscount = (int) floor($product->price * (float) config('sales.max_discount_percent', 20) / 100);
        if (($data['discount'] ?? 0) > $maxDiscount) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'discount' => 'Diskon melebihi batas maksimal Rp ' . number_format($maxDiscount, 0, ',', '.') . '.',
            ]);
        }
        $totalAfterDiscount = max(0, $product->price - ($data['discount'] ?? 0));
        if (($data['downpayment'] ?? 0) > $totalAfterDiscount) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'downpayment' => 'DP tidak boleh melebihi total harga.',
            ]);
        }
        // Pelunasan/penandaan lunas hanya oleh admin; sales tidak bisa langsung membuat transaksi lunas.
        if ($request->user()->hasRole('sales') && $totalAfterDiscount > 0 && ($data['downpayment'] ?? 0) >= $totalAfterDiscount) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'downpayment' => 'Pelunasan dicatat oleh admin. Isi DP lebih kecil dari total.',
            ]);
        }
        $priceCalc = $this->calc->calculate($product->price, $data['discount'] ?? 0, $data['downpayment'] ?? 0);

        $status = match (true) {
            $priceCalc['remaining_payment'] <= 0 => 'paid',
            $priceCalc['downpayment'] > 0         => 'partial',
            default                                => 'unpaid',
        };

        $transaction = SalesTransaction::create([
            'branch_id'         => $employee->branch_id,
            'sales_employee_id' => $employee->id,
            'sales_product_id'  => $product->id,
            'customer_name'     => $data['customer_name'],
            'customer_phone'    => $data['customer_phone'] ?? null,
            'price'             => $priceCalc['price'],
            'discount'          => $priceCalc['discount'],
            'total_price'       => $priceCalc['total_price'],
            'downpayment'       => $priceCalc['downpayment'],
            'remaining_payment' => $priceCalc['remaining_payment'],
            'status'            => $status,
            'paid_at'           => $status !== 'unpaid' ? now() : null,
            'pelunasan_at'      => $status === 'paid' ? now() : null,
            'created_by'        => $request->user()->id,
        ]);

        return redirect()->route('sales-transactions.show', $transaction)
            ->with('success', 'Transaksi berhasil dibuat.');
    }

    public function show(SalesTransaction $salesTransaction)
    {
        $this->authorizeAccess($salesTransaction);

        $salesTransaction->load(['salesEmployee', 'product', 'branch', 'commissions.employee']);

        return view('sales-transactions.show', ['transaction' => $salesTransaction]);
    }

    /** Catat pembayaran tambahan (pelunasan) — tidak otomatis rilis komisi, itu aksi terpisah. */
    public function recordPayment(Request $request, SalesTransaction $salesTransaction)
    {
        $this->authorizeAccess($salesTransaction);
        // Pencatatan pembayaran = konfirmasi uang masuk → bukan oleh sales sendiri.
        abort_if($request->user()->hasRole('sales'), 403, 'Pembayaran dicatat oleh admin.');
        abort_if($salesTransaction->isPaidOff(), 422, 'Transaksi sudah lunas.');

        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:' . $salesTransaction->remaining_payment],
        ]);

        $newDownpayment = $salesTransaction->downpayment + $data['amount'];
        $remaining = max(0, $salesTransaction->total_price - $newDownpayment);
        $isPaidOff = $remaining <= 0;

        $salesTransaction->update([
            'downpayment'       => $newDownpayment,
            'remaining_payment' => $remaining,
            'status'            => $isPaidOff ? 'paid' : 'partial',
            'paid_at'           => $salesTransaction->paid_at ?? now(),
            'pelunasan_at'      => $isPaidOff ? now() : $salesTransaction->pelunasan_at,
        ]);

        return back()->with('success', $isPaidOff ? 'Pembayaran tercatat, transaksi lunas.' : 'Pembayaran tercatat.');
    }

    public function releaseCommission(Request $request, SalesTransaction $salesTransaction, SalesCommissionReleaseService $service)
    {
        $this->authorizeAccess($salesTransaction);
        // Sales tidak boleh merilis komisinya sendiri.
        abort_if($request->user()->hasRole('sales'), 403, 'Komisi dirilis oleh admin.');

        DB::transaction(function () use ($salesTransaction, $service, $request) {
            $locked = SalesTransaction::whereKey($salesTransaction->id)->lockForUpdate()->firstOrFail();

            abort_unless($locked->isPaidOff(), 422, 'Pelunasan belum tercatat.');
            abort_if($locked->commission_released_at, 422, 'Komisi untuk transaksi ini sudah dirilis.');

            $service->release($locked, $request->user()->id);
        });

        return back()->with('success', 'Komisi berhasil dirilis.');
    }

    private function authorizeAccess(SalesTransaction $transaction): void
    {
        $user = auth()->user();

        if ($user->hasRole('superadmin')) {
            return;
        }

        if ($user->hasRole('sales')) {
            abort_unless(
                $user->employee && (int) $transaction->sales_employee_id === (int) $user->employee->id,
                403
            );
            return;
        }

        abort_unless($user->canAccessBranch($transaction->branch_id !== null ? (int) $transaction->branch_id : null), 403);
    }
}
