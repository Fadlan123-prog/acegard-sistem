<?php

namespace App\Http\Controllers;

use App\Models\PpfInvoice;
use App\Models\CustomerPpfInstallation;
use App\Services\CommissionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class PpfInvoiceController extends Controller
{
    public function __construct(private CommissionService $commissionService) {}

    private function authorizeBranch(?int $branchId): void
    {
        $user = auth()->user();
        if (!$user->hasRole('superadmin')) {
            $allowed = $user->branches()->pluck('branches.id')->all();
            abort_unless(in_array($branchId, $allowed), 403);
        }
    }

    public function create($installationId)
    {
        $installation = CustomerPpfInstallation::with(['customerPpf','ppfProduct','technicians'])
            ->findOrFail($installationId);

        $this->authorizeBranch($installation->branch_id);

        return view('ppf-invoices.create', compact('installation'));
    }

    public function store(Request $request, $installationId)
    {
        $installation = CustomerPpfInstallation::with('customerPpf')->findOrFail($installationId);
        $this->authorizeBranch($installation->branch_id);

        $data = $request->validate([
            'payment_method' => ['required','string','max:50'],
            'price' => ['required','integer','min:' . config('pricing.minimum_transaction_amount')],
            'discount' => ['nullable','integer','min:0'],
            'downpayment' => ['nullable','integer','min:0'],
            'notes' => ['nullable','string'],
            'invoice_date' => ['nullable','date'],
        ], [
            'price.min' => 'Harga tampak tidak wajar. Periksa kembali sebelum menyimpan.',
        ]);

        $price = (int)$data['price'];
        $discount = (int)($data['discount'] ?? 0);
        $downpayment = (int)($data['downpayment'] ?? 0);

        if ($discount > $price) {
            throw \Illuminate\Validation\ValidationException::withMessages(['discount' => 'Diskon tidak boleh melebihi harga.']);
        }

        $total = max(0, $price - $discount);
        $remaining = max(0, $total - $downpayment);

        $status = 'unpaid';
        if ($remaining <= 0 && $total > 0) $status = 'paid';
        elseif ($downpayment > 0 && $remaining > 0) $status = 'partial';

        // Guard against accidental double-submit creating two invoices for the
        // same installation (same pattern as InvoiceController::store()).
        $recentDuplicate = PpfInvoice::where('customer_ppf_installation_id', $installation->id)
            ->where('price', $price)
            ->where('total_price', $total)
            ->where('created_at', '>=', now()->subSeconds(30))
            ->first();

        if ($recentDuplicate) {
            return redirect()->route('ppf-invoices.show', $recentDuplicate->id)
                ->with('success', 'Invoice ini baru saja tersimpan.');
        }

        // generate invoice number (simple)
        $dateStr = now()->format('Ymd');
        $seq = str_pad((string) (PpfInvoice::where('invoice_number', 'like', "PPF-$dateStr-%")->count() + 1), 4, '0', STR_PAD_LEFT);
        $invoiceNumber = "PPF-$dateStr-$seq";

        $invoice = PpfInvoice::create([
            'branch_id' => $installation->branch_id,
            'customer_ppf_id' => $installation->customer_ppf_id,
            'customer_ppf_installation_id' => $installation->id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $data['invoice_date'] ?? now()->toDateString(),
            'payment_method' => $data['payment_method'],
            'price' => $price,
            'discount' => $discount,
            'downpayment' => $downpayment,
            'total_price' => $total,
            'remaining_payment' => $remaining,
            'status' => $status,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('ppf-invoices.show', $invoice->id)
            ->with('success', 'Invoice PPF berhasil dibuat.');
    }

    public function show($id)
    {
        $invoice = PpfInvoice::with([
            'branch',
            'customer',
            'installation.ppfProduct',
            'installation.technicians',
        ])->findOrFail($id);
        $this->authorizeBranch($invoice->branch_id);

        return view('ppf-invoices.show', compact('invoice'));
    }

    public function edit($id)
    {
        $invoice = \App\Models\PpfInvoice::with([
            'branch',
            'customer',
            'installation.ppfProduct',
            'installation.technicians',
        ])->findOrFail($id);
        $this->authorizeBranch($invoice->branch_id);

        return view('ppf-invoices.edit', compact('invoice'));
    }

    public function update(Request $request, $id)
    {
        $invoice = \App\Models\PpfInvoice::with(['installation'])->findOrFail($id);
        $this->authorizeBranch($invoice->branch_id);

        $data = $request->validate([
            'invoice_date'    => ['required','date'],
            'payment_method'  => ['required','string','max:50'],
            'price'           => ['required','integer','min:' . config('pricing.minimum_transaction_amount')],
            'discount'        => ['nullable','integer','min:0'],
            'downpayment'     => ['nullable','integer','min:0'],
            'status'          => ['required','in:unpaid,partial,paid,void'],
            'notes'           => ['nullable','string'],
        ], [
            'price.min' => 'Harga tampak tidak wajar. Periksa kembali sebelum menyimpan.',
        ]);

        $price      = (int)$data['price'];
        $discount   = (int)($data['discount'] ?? 0);
        $downpay    = (int)($data['downpayment'] ?? 0);

        if ($discount > $price) {
            throw \Illuminate\Validation\ValidationException::withMessages(['discount' => 'Diskon tidak boleh melebihi harga.']);
        }

        $total      = max(0, $price - $discount);
        $remaining  = max(0, $total - $downpay);

        if ($data['status'] === 'paid' && $remaining > 0) {
            throw \Illuminate\Validation\ValidationException::withMessages(['status' => 'Status Lunas hanya boleh bila DP sudah menutup total harga.']);
        }

        // kalau status void, biasanya angka bisa tetap (audit), tapi payment dianggap 0.
        if ($data['status'] === 'void') {
            // optional: paksa remaining = total (atau 0) tergantung rule kamu
            // di sini saya biarkan tetap sesuai hitungan supaya transparan
        }

        $invoice->update([
            'invoice_date'       => $data['invoice_date'],
            'payment_method'     => $data['payment_method'],
            'price'              => $price,
            'discount'           => $discount,
            'downpayment'        => $downpay,
            'total_price'        => $total,
            'remaining_payment'  => $remaining,
            'status'             => $data['status'],
            'notes'              => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('ppf-invoices.show', $invoice->id)
            ->with('success', 'Invoice PPF berhasil diupdate.');
    }

    public function destroy($id)
    {
        $invoice = \App\Models\PpfInvoice::findOrFail($id);
        $this->authorizeBranch($invoice->branch_id);

        // Komisi yang sudah dibayar / masuk pencairan membuat invoice tidak boleh dihapus.
        $locked = DB::table('commissions')->where('ppf_invoice_id', $invoice->id)
            ->where(fn ($q) => $q->where('status', 'paid')->orWhereNotNull('commission_payment_batch_id'))
            ->exists();

        if ($locked) {
            return back()->with('error', 'Invoice PPF tidak bisa dihapus: komisinya sudah dibayarkan / masuk pencairan.');
        }

        DB::transaction(function () use ($invoice) {
            // Komisi yang belum dibayar ikut dibatalkan agar tidak jadi yatim yang bisa dicairkan.
            DB::table('commissions')->where('ppf_invoice_id', $invoice->id)->delete();
            $invoice->delete();
        });

        // balik ke halaman customer/installation sesuai preferensi
        return redirect()
            ->route('ppf-customers.index')
            ->with('success', 'Invoice PPF berhasil dihapus.');
    }


    public function download(PpfInvoice $invoice)
    {
        $this->authorizeBranch($invoice->branch_id);

        $invoice->load([
            'branch',
            'customer',
            'installation.ppfProduct',
            'installation.technicians:id,name',
        ]);

        $customer = $invoice->customer;
        if (!$customer) abort(404, 'Customer PPF untuk invoice ini tidak ditemukan.');

        $branch    = $invoice->branch;
        $branchKey = $branch ? Str::slug($branch->name) : 'default';
        $view      = $this->resolvePpfInvoiceViewForBranch($branchKey);

        // ✅ Ambil public path yang benar (lokal/prod)
        // Kalau mau custom di server, set di .env: PDF_PUBLIC_PATH=/home/acegardi/public_html
        $publicPath = realpath(env('PDF_PUBLIC_PATH', public_path()));

        if (!$publicPath || !is_dir($publicPath)) {
            // fallback terakhir
            $publicPath = realpath(public_path());
        }

        if (!$publicPath || !is_dir($publicPath)) {
            abort(500, 'Public path tidak ditemukan. Cek PDF_PUBLIC_PATH atau folder public/.');
        }

        // ✅ cukup set config dompdf, tidak perlu app()->usePublicPath()
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
                'invoice'      => $invoice,
                'customer'     => $customer,
                'installation' => $invoice->installation,
                'branch'       => $branch,
            ]);

        return $pdf->stream('ppf-invoice-'.$invoice->invoice_number.'.pdf');
    }

    public function releaseCommission(PpfInvoice $invoice)
    {
        $user = auth()->user();

        // Branch access guard
        if (!$user->hasRole('superadmin')) {
            $allowed = $user->branches()->pluck('branches.id')->all();
            abort_unless(in_array($invoice->branch_id, $allowed), 403);
        }

        if ($invoice->commission_released_at) {
            return back()->with('error', 'Komisi PPF untuk invoice ini sudah pernah dirilis.');
        }

        if ($invoice->status === 'void') {
            return back()->with('error', 'Invoice void tidak bisa dirilis komisinya.');
        }

        if ($invoice->status !== 'paid') {
            return back()->with('error', 'Komisi PPF baru bisa dirilis setelah invoice lunas.');
        }

        DB::beginTransaction();
        try {
            // Baca ulang di bawah lock agar klik ganda / dua admin tidak merilis dua kali.
            $locked = \App\Models\PpfInvoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            if ($locked->commission_released_at || $locked->status !== 'paid') {
                DB::rollBack();
                return back()->with('error', 'Komisi PPF sudah dirilis atau invoice belum lunas.');
            }

            $this->commissionService->createPpfCommissions($invoice);

            $invoice->update([
                'commission_released_at' => now(),
                'commission_released_by' => auth()->id(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal merilis komisi PPF: ' . $e->getMessage());
        }

        return back()->with('success', 'Komisi PPF berhasil dirilis.');
    }

    protected function resolvePpfInvoiceViewForBranch(string $branchKey): string
    {
        // 1) resources/views/invoices/ppf/branches/{slug}.blade.php
        $candidates = [
            "invoices.ppf.branches.$branchKey",
            "invoices.ppf.pdf_$branchKey",
            "ppf-invoices.print", // default (yang kamu sudah buat)
        ];

        foreach ($candidates as $v) {
            if (view()->exists($v)) return $v;
        }

        return "ppf-invoices.print";
    }
}
