<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Product;
use App\Notifications\UnreleasedCommissionAlert;
use App\Services\AditCommissionService;
use App\Services\BranchContext;
use App\Services\CommissionReleaseService;
use App\Services\DashboardCacheService;
use App\Services\DocumentNumberService;
use App\Services\FilmStockPoolService;
use App\Services\InvoiceCalculationService;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceCalculationService $calc,
        private AditCommissionService $aditCommission,
        private CommissionReleaseService $commissionRelease,
        private FilmStockPoolService $filmStock,
        private DocumentNumberService $docNumbers,
        private DashboardCacheService $dashCache,
        private BranchContext $branch,
    ) {}

    public function create(Customer $customer)
    {
        $customer = Customer::with('invoice')->findOrFail($customer->id);

        $employees = Employee::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('invoice.create', compact('customer', 'employees', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'    => ['required', 'exists:customers,id'],
            'price'          => ['required', 'integer', 'min:0'],
            'discount'       => ['nullable', 'integer', 'min:0'],
            'downpayment'    => ['nullable', 'integer', 'min:0'],
            'payment_method' => ['nullable', 'in:cash,credit,transfer,tokopedia'],
            'status'         => ['required', 'in:paid,unpaid,partial'],
            'invoice_number' => ['nullable', 'string', 'max:255', 'unique:invoices,invoice_number'],
            'invoice_date'   => ['required', 'date'],
        ]);

        $invoiceDate = Carbon::parse($data['invoice_date'])->format('Y-m-d H:i:s');

        $priceCalc        = $this->calc->calculate((int) $data['price'], (int) ($data['discount'] ?? 0), (int) ($data['downpayment'] ?? 0));
        $price            = $priceCalc['price'];
        $discount         = $priceCalc['discount'];
        $downpayment      = $priceCalc['downpayment'];
        $totalPrice       = $priceCalc['total_price'];
        $remainingPayment = $priceCalc['remaining_payment'];

        // Guard against accidental double-submit (double-click / form resubmit) creating
        // two near-identical invoices for the same job. Customers can legitimately have
        // multiple invoices over time (Customer::invoices() is hasMany), so this only
        // blocks an exact-amount duplicate created moments ago, not repeat business.
        $recentDuplicate = Invoice::where('customer_id', $data['customer_id'])
            ->where('price', $price)
            ->where('total_price', $totalPrice)
            ->where('created_at', '>=', now()->subSeconds(30))
            ->first();

        if ($recentDuplicate) {
            return redirect()
                ->route('invoice.show', $recentDuplicate->id)
                ->with('success', 'Invoice ini baru saja tersimpan.');
        }

        $invoiceNumber = !empty($data['invoice_number'])
            ? $data['invoice_number']
            : $this->docNumbers->generate('invoices', 'invoice_number', 'INV');

        DB::beginTransaction();

        try {
            $invoice = Invoice::create([
                'customer_id'       => (int) $data['customer_id'],
                'invoice_number'    => $invoiceNumber,
                'invoice_date'      => $invoiceDate,
                'payment_method'    => $data['payment_method'] ?? null,
                'price'             => $price,
                'discount'          => $discount,
                'downpayment'       => $downpayment,
                'total_price'       => $totalPrice,
                'remaining_payment' => $remainingPayment,
                'status'            => $data['status'],
            ]);

            DB::commit();

            return redirect()
                ->route('invoice.show', $invoice->id)
                ->with('success', 'Invoice berhasil disimpan. Catat pelunasan lalu rilis komisi.');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('[Invoice Store] gagal', [
                'message' => $e->getMessage(),
                'data'    => $data,
            ]);

            return back()
                ->with('error', 'Gagal menyimpan invoice: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Invoice $invoice)
    {
        $this->authorizeBranchForInvoice($invoice);

        $invoice->load([
            'customer.branch',
            'customer.customerProducts.part',
            'customer.customerProducts.product',
            'customer.customerProducts.categoryProduct',
            'customer.tukang:id,name',
            'customer.kenek:id,name',
        ]);

        if (!$invoice->customer) {
            abort(404, 'Customer untuk invoice ini tidak ditemukan.');
        }

        $customer = $invoice->customer;

        return view('invoice.show', compact('invoice', 'customer'));
    }

    public function edit(Invoice $invoice)
    {
        $this->authorizeBranchForInvoice($invoice);

        if ($invoice->isStep3Done()) {
            return redirect()
                ->route('invoice.show', $invoice->id)
                ->with('error', 'Invoice tidak dapat diubah setelah komisi dirilis.');
        }

        if ($invoice->isStep2Done()) {
            return redirect()
                ->route('invoice.show', $invoice->id)
                ->with('error', 'Invoice yang sudah lunas tidak dapat diubah. Hubungi superadmin jika perlu koreksi.');
        }

        $invoice->load('customer');

        return view('invoice.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorizeBranchForInvoice($invoice);

        if ($invoice->isStep3Done()) {
            return back()->with('error', 'Invoice tidak dapat diubah setelah komisi dirilis.');
        }

        if ($invoice->isStep2Done()) {
            return back()->with('error', 'Invoice yang sudah lunas tidak dapat diubah harga atau customernya. Hubungi superadmin.');
        }

        $data = $request->validate([
            'customer_id'    => ['required', 'exists:customers,id'],
            'price'          => ['required', 'integer', 'min:0'],
            'discount'       => ['nullable', 'integer', 'min:0'],
            'downpayment'    => ['nullable', 'integer', 'min:0'],
            'payment_method' => ['nullable', 'in:cash,credit,transfer,tokopedia'],
            'status'         => ['required', 'in:paid,unpaid,partial'],
            'invoice_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('invoices', 'invoice_number')->ignore($invoice->id),
            ],
        ]);

        // invoice_date tidak boleh berubah setelah dibuat — gunakan nilai asli
        $invoiceDate = $invoice->invoice_date;

        $priceCalc        = $this->calc->calculate((int) $data['price'], (int) ($data['discount'] ?? 0), (int) ($data['downpayment'] ?? 0));
        $price            = $priceCalc['price'];
        $discount         = $priceCalc['discount'];
        $downpayment      = $priceCalc['downpayment'];
        $totalPrice       = $priceCalc['total_price'];
        $remainingPayment = $priceCalc['remaining_payment'];

        $invoiceNumber = !empty($data['invoice_number'])
            ? $data['invoice_number']
            : ($invoice->invoice_number ?: $this->docNumbers->generate('invoices', 'invoice_number', 'INV'));

        DB::beginTransaction();

        try {
            $invoice->update([
                'customer_id'       => (int) $data['customer_id'],
                'invoice_number'    => $invoiceNumber,
                'invoice_date'      => $invoiceDate,
                'payment_method'    => $data['payment_method'] ?? null,
                'price'             => $price,
                'discount'          => $discount,
                'downpayment'       => $downpayment,
                'total_price'       => $totalPrice,
                'remaining_payment' => $remainingPayment,
                'status'            => $data['status'],
            ]);

            DB::commit();

            return redirect()
                ->route('invoice.show', $invoice->id)
                ->with('success', 'Invoice berhasil diperbarui.');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('[Invoice Update] gagal', [
                'message'    => $e->getMessage(),
                'invoice_id' => $invoice->id,
                'data'       => $data,
            ]);

            return back()
                ->with('error', 'Gagal memperbarui invoice: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function recordPelunasan(Request $request, Invoice $invoice)
    {
        $this->authorizeBranchForInvoice($invoice);

        if ($invoice->isQcBlocking()) {
            $message = $invoice->isQcFailed()
                ? 'Invoice belum bisa dilunasi — QC ditolak dan menunggu perbaikan teknisi.' . ($invoice->qc_notes ? ' Catatan: ' . $invoice->qc_notes : '')
                : 'Invoice belum bisa dilunasi — menunggu pemeriksaan QC dari supervisor.';

            return back()->with('error', $message);
        }

        // Both pelunasan AND stock deduction are done — nothing left to do.
        if ($invoice->isStep2Done() && $invoice->isStockDeducted()) {
            return back()->with('error', 'Pelunasan sudah dicatat dan stok sudah dipotong sebelumnya.');
        }

        // Pelunasan done but stock deduction failed previously — allow stock-only retry.
        $paymentAlreadyRecorded = $invoice->isStep2Done();

        $data = $request->validate([
            'payment_method' => ['nullable', 'in:cash,credit,transfer,tokopedia'],
        ]);

        $now = now();

        // Tahap 1: Catat pembayaran — commit terpisah agar tidak ikut rollback jika stok gagal
        try {
            DB::beginTransaction();
            if (!$paymentAlreadyRecorded) {
                $invoice->update([
                    'remaining_payment' => 0,
                    'status'            => 'paid',
                    'paid_at'           => $now,
                    'pelunasan_at'      => $now,
                    'pelunasan_by'      => auth()->id(),
                    'payment_method'    => $data['payment_method'] ?? $invoice->payment_method,
                ]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mencatat pelunasan: ' . $e->getMessage());
        }

        // Tahap 2: Potong stok kaca film — gagal tidak membatalkan pembayaran
        if (!$invoice->isStockDeducted()) {
            try {
                $invoice->loadMissing('customer.customerProducts');
                $customer = $invoice->customer;

                if ($customer && $customer->customerProducts->isNotEmpty()) {
                    $carProducts = $customer->customerProducts->map(fn ($cp) => [
                        'customer_product' => $cp,
                        'data' => [
                            'product_id'          => $cp->product_id,
                            'category_product_id' => $cp->category_product_id,
                            'part_id'             => $cp->part_id,
                        ],
                    ])->toArray();

                    DB::beginTransaction();
                    $this->filmStock->consumeForCar($customer, $carProducts, $customer->install_type ?? 'fullset');
                    $invoice->update([
                        'stock_deducted_at' => $now,
                        'stock_deducted_by' => auth()->id(),
                    ]);
                    DB::commit();
                }
            } catch (\Throwable $e) {
                DB::rollBack();
                \Illuminate\Support\Facades\Log::warning('[recordPelunasan] Stok gagal dipotong', [
                    'invoice_id' => $invoice->id,
                    'error'      => $e->getMessage(),
                ]);
                // Pelunasan tetap berhasil — stok bisa dipotong manual nanti
            }
        }

        // Bust dashboard cache — invoice is now paid, summary totals changed
        try {
            $invoice->loadMissing('customer');
            $customer = $invoice->customer;
            $branchId = $customer?->branch_id ?? $this->branch->idOrNull();
            if ($branchId) {
                $this->dashCache->invalidate($branchId);
            }
            // Bust teknisi/kenek per-employee caches (commission status changed)
            if ($customer?->tukang_id) {
                $this->dashCache->invalidateTeknisi($customer->tukang_id);
            }
            if ($customer?->kenek_id) {
                $this->dashCache->invalidateTeknisi($customer->kenek_id);
            }
        } catch (\Throwable) {}

        // Notify admins: commission not yet released
        try {
            $admins = User::whereHas('roles', fn($q) => $q->whereIn('name', ['superadmin', 'admin']))->get();
            \Illuminate\Support\Facades\Notification::send(
                $admins,
                new UnreleasedCommissionAlert($invoice)
            );
        } catch (\Throwable) {
            // Notification failure must never cancel pelunasan
        }

        return back()->with('success', 'Pelunasan berhasil dicatat dan stok terpotong. Silakan rilis komisi.');
    }

    public function releaseCommission(Request $request, Invoice $invoice)
    {
        $this->authorizeBranchForInvoice($invoice);

        if (!$invoice->isStep2Done()) {
            return back()->with('error', 'Pelunasan belum dicatat. Selesaikan step 2 terlebih dahulu.');
        }

        $lebaran = $request->boolean('lebaran');

        try {
            DB::beginTransaction();

            // Re-read with a pessimistic lock to prevent concurrent double-release.
            $locked = Invoice::lockForUpdate()->findOrFail($invoice->id);

            if ($locked->isStep3Done()) {
                DB::rollBack();
                return back()->with('error', 'Komisi sudah pernah dirilis untuk invoice ini.');
            }

            $this->commissionRelease->release($locked, auth()->id(), $lebaran);
            DB::commit();

            $note = $lebaran ? ' (Rate Lebaran)' : '';
            return back()->with('success', "Komisi berhasil dirilis{$note}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal merilis komisi: ' . $e->getMessage());
        }
    }

    /**
     * Reset dan rilis ulang komisi — hanya superadmin.
     * Digunakan untuk memperbaiki komisi yang salah (mis. mobil ke-2 dapat fullset bukan fullset_ekstra).
     */
    public function rereleaseCommission(Request $request, Invoice $invoice)
    {
        if (! auth()->user()->hasRole('superadmin')) {
            abort(403, 'Hanya superadmin yang dapat merilis ulang komisi.');
        }

        $this->authorizeBranchForInvoice($invoice);

        if (! $invoice->isStep2Done()) {
            return back()->with('error', 'Pelunasan belum dicatat.');
        }

        $lebaran = $request->boolean('lebaran');

        try {
            DB::beginTransaction();

            $locked = Invoice::lockForUpdate()->findOrFail($invoice->id);

            // Hapus semua komisi lama untuk invoice ini
            DB::table('commissions')
                ->where('invoice_id', $locked->id)
                ->delete();

            // Hapus komisi customer yang tidak linked invoice (flow lama)
            if ($locked->customer_id) {
                DB::table('commissions')
                    ->where('customer_id', $locked->customer_id)
                    ->whereNull('invoice_id')
                    ->whereIn('role', ['installer', 'marketing', 'adit_override', 'teknisi', 'kenek'])
                    ->delete();
            }

            // Reset flag released agar release() bisa jalan
            $locked->update(['commission_released_at' => null, 'commission_released_by' => null]);

            $this->commissionRelease->release($locked, auth()->id(), $lebaran);

            DB::commit();

            $note = $lebaran ? ' (Rate Lebaran)' : '';
            return back()->with('success', "Komisi berhasil dirilis ulang{$note}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal rilis ulang: ' . $e->getMessage());
        }
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorizeBranchForInvoice($invoice);

        if ($invoice->isStep2Done()) {
            return back()->with('error',
                'Invoice yang sudah lunas tidak dapat dihapus. Gunakan fitur koreksi atau hubungi superadmin.');
        }

        DB::beginTransaction();

        try {
            DB::table('commissions')
                ->where('invoice_id', $invoice->id)
                ->delete();

            $invoice->delete();

            DB::commit();

            return redirect()
                ->route('customer.index')
                ->with('success', 'Invoice berhasil dihapus.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Gagal menghapus invoice: ' . $e->getMessage());
        }
    }

    public function download(Invoice $invoice)
    {
        $this->authorizeBranchForInvoice($invoice);

        $invoice->load([
            'customer.branch',
            'customer.customerProducts.part',
            'customer.customerProducts.product',
            'customer.customerProducts.categoryProduct',
            'customer.tukang:id,name',
            'customer.kenek:id,name',
        ]);

        $customer = $invoice->customer;

        if (!$customer) {
            abort(404, 'Customer untuk invoice ini tidak ditemukan.');
        }

        $branch = $customer->branch;

        $keys = $this->candidateBranchKeys($branch);
        $view = $this->resolveInvoiceViewForBranch($keys);

        // Debug sementara kalau mau cek
        // dd($branch?->name, $branch?->city, $keys, $view);

        $publicPath = public_path();

        config([
            'dompdf.public_path'     => $publicPath,
            'dompdf.chroot'          => $publicPath,
            'dompdf.isRemoteEnabled' => true,
        ]);

        $pdf = Pdf::setPaper('A4', 'portrait')
            ->setOptions([
                'chroot'          => $publicPath,
                'isRemoteEnabled' => true,
            ])
            ->loadView($view, [
                'invoice'         => $invoice,
                'customer'        => $customer,
                'customerProduct' => $customer->customerProducts,
                'branch'          => $branch,
            ]);

        return $pdf->stream('invoice-' . $invoice->invoice_number . '.pdf');
    }

    private function authorizeBranchForInvoice(Invoice $invoice): void
    {
        if (auth()->user()->hasRole('superadmin')) {
            return;
        }

        $invoice->loadMissing('customer');
        $branchId = (int) ($invoice->customer?->branch_id ?? 0);

        $allowed = auth()->user()
            ->branches()
            ->pluck('branches.id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        if (!in_array($branchId, $allowed, true)) {
            abort(403, 'Anda tidak memiliki akses ke invoice dari cabang lain.');
        }
    }

    protected function candidateBranchKeys($branch): array
    {
        if (!$branch) {
            return ['default'];
        }

        $keys = [];

        $name = trim((string) $branch->name);
        $city = trim((string) ($branch->city ?? ''));

        /*
        |--------------------------------------------------------------------------
        | Contoh:
        | Acegard Bengkulu -> acegard-bengkulu
        | Bengkulu         -> bengkulu
        |--------------------------------------------------------------------------
        */
        if ($name !== '') {
            $keys[] = Str::slug($name);

            // Hilangkan kata Acegard di depan
            $withoutAcegard = preg_replace('/^acegard\s+/i', '', $name);
            $keys[] = Str::slug($withoutAcegard);
        }

        if ($city !== '') {
            $keys[] = Str::slug($city);
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback manual berdasarkan keyword
        |--------------------------------------------------------------------------
        */
        $lowerName = strtolower($name . ' ' . $city);

        if (str_contains($lowerName, 'pusat')) {
            $keys[] = 'pusat';
        }

        if (str_contains($lowerName, 'bandung')) {
            $keys[] = 'bandung';
        }

        if (str_contains($lowerName, 'bengkulu')) {
            $keys[] = 'bengkulu';
        }

        if (str_contains($lowerName, 'jambi')) {
            $keys[] = 'jambi';
        }

        return collect($keys)
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    protected function resolveInvoiceViewForBranch(array $keys): string
    {
        foreach ($keys as $key) {
            $candidates = [
                "invoice.branches.$key",
                "invoice.pdf_$key",
                "invoice.$key",
            ];

            foreach ($candidates as $view) {
                if (view()->exists($view)) {
                    return $view;
                }
            }
        }

        return 'invoice.pdf';
    }

}
