<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Employee;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Services\InstallTypeResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CommissionService;

class InvoiceController extends Controller
{
    public function create(Customer $customer)
    {
        $customer = Customer::with('invoice')->findOrFail($customer->id);
        $employees = Employee::orderBy('name')->get();
        $products  = Product::orderBy('name')->get();

        return view('invoice.create', compact('customer', 'employees', 'products'));
    }

    public function store(Request $request, InstallTypeResolver $resolver, CommissionService $commission)
    {
        $data = $request->validate([
            'customer_id'    => ['required','exists:customers,id'],
            'price'          => ['required','integer','min:0'],
            'discount'       => ['nullable','integer','min:0'],
            'downpayment'    => ['nullable','integer','min:0'],
            'payment_method' => ['required','in:cash,credit,transfer,marketplace'],
            'status'         => ['required','in:paid,unpaid,partial'],
            'invoice_number' => ['nullable','string','unique:invoices,invoice_number'],
            'invoice_date'   => ['required','date'],
            'has_panoramic'  => ['nullable','boolean'],
        ]);

        // Normalisasi tanggal dari input datetime-local
        $invoiceDate = Carbon::parse($data['invoice_date'])->format('Y-m-d H:i:s');

        $price  = (int) $data['price'];
        $disc   = (int) ($data['discount'] ?? 0);
        $dp     = (int) ($data['downpayment'] ?? 0);
        $total  = max(0, $price - $disc);
        $remain = max(0, $total - $dp);



        $invNo = $data['invoice_number'] ?? ('INV-'.now()->format('YmdHis'));

        DB::beginTransaction();
        try {
            // 1) Buat invoice
            $invoice = Invoice::create([
                'customer_id'       => (int)$data['customer_id'],
                'payment_method'    => $data['payment_method'],
                'price'             => $price,
                'discount'          => $disc,
                'downpayment'       => $dp,
                'total_price'       => $total,
                'remaining_payment' => $remain,
                'status'            => $data['status'],
                'invoice_number'    => $invNo,
                'invoice_date'      => $invoiceDate,
            ]);

            if ($adit = Employee::whereRaw('LOWER(name)=?', ['adit'])->first()) {
                $amount = (int) round($invoice->total_price * 0.02);

                // upsert agar 1 invoice hanya 1 komisi Adit
                DB::table('commissions')->updateOrInsert(
                    [
                        'employees_id' => $adit->id,
                        'invoice_id'   => $invoice->id,
                    ],
                    [
                        'customer_id'  => $invoice->customer_id,
                        'amount'       => $amount,
                        'updated_at'   => now(),
                        'created_at'   => now(),
                    ]
                );
            }

            DB::commit();

            return redirect()->route('invoice.show', $invoice->id)
                ->with('success', "Invoice & komisi tersimpan.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Invoice Store] gagal', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data'  => $data,
            ]);
            return back()->with('error', 'Gagal menyimpan: '.$e->getMessage())->withInput();
        }
    }


    public function show(Invoice $invoice)
    {
        // muat relasi yang dibutuhkan
        $invoice->load('customer');
        $customer = Customer::with([
            'products',
            'customerProducts.part',
            'customerProducts.product',
            'customerProducts.categoryProduct',
        ])->findOrFail($invoice->customer_id);

        return view('invoice.show', compact('invoice', 'customer'));
    }

    public function edit(Invoice $invoice)
    {
        return view('invoice.edit', compact('invoice'));
    }

    // UPDATE (tanpa mengubah komisi; jika perlu, bisa tambah logika re-kalkulasi)
    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'type' => 'required|string', // atau batasi: in:fullset,fullset_ekstra,ekstra_panoramic,fullset_ekstra_panoramic,skkb
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'downpayment' => 'nullable|numeric|min:0',
            'status' => 'required|in:paid,unpaid',
            'admin_password' => 'required|string',
        ]);

        $admin = Auth::user();
        if (!Hash::check($request->admin_password, $admin->password)) {
            return back()->withErrors(['admin_password' => 'Password salah'])->withInput();
        }

        $price = (float)$request->price;
        $disc  = (float)($request->discount ?? 0);
        $dp    = (float)($request->downpayment ?? 0);
        $total = max(0, $price - $disc);
        $sisa  = max(0, $total - $dp);

        DB::beginTransaction();
        try {
            // 1) update invoice
            $invoice->update([
                'type'              => $request->type,
                'price'             => $price,
                'discount'          => $disc,
                'downpayment'       => $dp,
                'total_price'       => $total,
                'remaining_payment' => $remain,
                'status'            => $request->status,
            ]);

            // 2) sinkronkan komisi Adit = 2% dari total terbaru
            if ($adit = Employee::whereRaw('LOWER(name)=?', ['adit'])->first()) {
                $amount = (int) round($invoice->total_price * 0.02);

                DB::table('commissions')->updateOrInsert(
                    [
                        'employees_id' => $adit->id,
                        'invoice_id'   => $invoice->id,
                    ],
                    [
                        'customer_id'  => $invoice->customer_id,
                        'amount'       => $amount,
                        'updated_at'   => now(),
                        'created_at'   => now(),
                    ]
                );
            }

            DB::commit();
            return redirect()->route('invoice.show', $invoice->id)
                ->with('success', 'Invoice & komisi Adit diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    // Hapus invoice
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('customer.index')->with('success', 'Invoice berhasil dihapus.');
    }

    // Download PDF
    public function download(Invoice $invoice)
    {
        $customer = Customer::with(['customerProducts.part', 'customerProducts.product', 'customerProducts.categoryProduct'])
            ->findOrFail($invoice->customer_id);

        $customerProduct = $customer->customerProducts;

        $pdf = PDF::loadView('invoice.pdf', compact('invoice', 'customer', 'customerProduct'));
        return $pdf->stream();
    }


}
