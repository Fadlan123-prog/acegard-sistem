<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function create(Customer $customer)
    {
        $customer = Customer::with('invoice')->findOrFail($customer->id);

        return view('invoice.create', compact('customer'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|string',
            'price' => 'required|integer',
            'discount' => 'nullable|integer',
            'downpayment' => 'nullable|integer',
            'status' => 'required|string',
            'invoice_number' => 'required|string|unique:invoices',
            'invoice_date' => 'required|date',
        ]);

        $total = $validated['price'] - ($validated['discount'] ?? 0);
        $remaining = $total - ($validated['downpayment'] ?? 0);

        $validated['total_price'] = $total;
        $validated['remaining_payment'] = $remaining;

        Invoice::create($validated);

        return redirect()->route('customer.index')->with('success', 'Invoice berhasil dibuat.');
    }

    public function show(Invoice $invoice)
    {
        $customer = Customer::with([
            'products',
            'customerProducts.part',
            'customerProducts.product',
            'customerProducts.categoryProduct'
        ])->findOrFail($invoice->customer_id);
        $invoice = Invoice::with('customer')->findOrFail($invoice->id);
        return view('invoice.show', compact('invoice', 'customer'));
    }

    public function edit(Invoice $invoice)
    {
        return view('invoice.edit', compact('invoice'));
    }

    // Update invoice
    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'type' => 'required|string',
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

        $discount = $request->discount ?? 0;
        $dp = $request->downpayment ?? 0;
        $total = $request->price - $discount;
        $sisa = $total - $dp;

        $invoice->update([
            'type' => $request->type,
            'price' => $request->price,
            'discount' => $discount,
            'downpayment' => $dp,
            'total_price' => $total,
            'remaining_payment' => $sisa,
            'status' => $request->status,
        ]);

        return redirect()->route('invoice.show', $invoice->id)->with('success', 'Invoice berhasil diperbarui.');
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
