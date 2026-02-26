<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimInvoice;
use App\Models\WarrantyClaimInvoiceItem;

class AdminWarrantyClaimInvoiceController extends Controller
{
    public function create(WarrantyClaim $claim)
    {
        $claim->load(['items','warrantable']);

        abort_unless($claim->is_chargeable, 422, 'Claim ini tidak chargeable.');

        return view('admin.claim_invoices.create', compact('claim'));
    }

    public function store(Request $request, WarrantyClaim $claim)
    {
        abort_unless($claim->is_chargeable, 422, 'Claim ini tidak chargeable.');
        abort_if($claim->invoice, 409, 'Invoice sudah ada.');

        $data = $request->validate([
            'payment_method' => ['required','string','max:255'],
            'discount' => ['nullable','integer','min:0'],
            'downpayment' => ['nullable','integer','min:0'],
            'status' => ['required','in:unpaid,paid,void'],
        ]);

        $claim->load('items');

        $subtotal = (int)$claim->items->sum('charge_amount');
        $discount = (int)($data['discount'] ?? 0);
        $downpayment = (int)($data['downpayment'] ?? 0);
        $total = max(0, $subtotal - $discount);
        $remaining = max(0, $total - $downpayment);

        $invoiceNumber = $this->generateInvoiceNumber();

        $invoice = WarrantyClaimInvoice::create([
            'warranty_claim_id' => $claim->id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => now(),
            'payment_method' => $data['payment_method'],
            'subtotal' => $subtotal,
            'discount' => $discount,
            'downpayment' => $downpayment,
            'total_price' => $total,
            'remaining_payment' => $remaining,
            'status' => $data['status'],
        ]);

        foreach ($claim->items as $it) {
            $price = (int)$it->charge_amount;
            if ($price <= 0) continue;

            WarrantyClaimInvoiceItem::create([
                'warranty_claim_invoice_id' => $invoice->id,
                'description' => $it->item_label ?: 'Claim Item',
                'qty' => 1,
                'unit_price' => $price,
                'total' => $price,
            ]);
        }

        // sync quote_total
        $claim->quote_total = $subtotal;
        $claim->save();

        return redirect()->route('admin.claim-invoices.show', $invoice->id)
            ->with('success', 'Invoice claim dibuat.');
    }

    public function show(WarrantyClaimInvoice $invoice)
    {
        $invoice->load(['items','claim.warrantable']);
        return view('admin.claim_invoices.show', compact('invoice'));
    }

    public function print(WarrantyClaimInvoice $invoice)
    {
        $invoice->load(['items','claim.warrantable']);
        return view('admin.claim_invoices.print', compact('invoice'));
    }

    public function download(WarrantyClaimInvoice $invoice)
    {
        $invoice->load(['items','claim.warrantable']);

        $pdf = Pdf::setPaper('A4', 'portrait')
            ->loadView('admin.claim_invoices.print', compact('invoice'));

        return $pdf->stream('claim-invoice-'.$invoice->invoice_number.'.pdf');
    }

    public function edit(WarrantyClaimInvoice $invoice)
    {
        $invoice->load(['items','claim']);
        return view('admin.claim_invoices.edit', compact('invoice'));
    }

    public function update(Request $request, WarrantyClaimInvoice $invoice)
    {
        $data = $request->validate([
            'payment_method' => ['required','string','max:255'],
            'discount' => ['nullable','integer','min:0'],
            'downpayment' => ['nullable','integer','min:0'],
            'status' => ['required','in:unpaid,paid,void'],
        ]);

        $subtotal = (int)$invoice->items()->sum('total');
        $discount = (int)($data['discount'] ?? 0);
        $downpayment = (int)($data['downpayment'] ?? 0);
        $total = max(0, $subtotal - $discount);
        $remaining = max(0, $total - $downpayment);

        $invoice->update([
            'payment_method' => $data['payment_method'],
            'subtotal' => $subtotal,
            'discount' => $discount,
            'downpayment' => $downpayment,
            'total_price' => $total,
            'remaining_payment' => $remaining,
            'status' => $data['status'],
        ]);

        return redirect()->route('admin.claim-invoices.show', $invoice->id)
            ->with('success', 'Invoice diperbarui.');
    }

    public function destroy(WarrantyClaimInvoice $invoice)
    {
        $claim = $invoice->claim;
        $invoice->delete();

        return redirect()->route('admin.claims.show', $claim->id)
            ->with('success', 'Invoice claim dihapus.');
    }

    protected function generateInvoiceNumber(): string
    {
        $prefix = 'INV-CLM-'.now()->format('Ym').'-';
        $last = WarrantyClaimInvoice::where('invoice_number','like',$prefix.'%')
            ->orderByDesc('id')->value('invoice_number');

        $next = 1;
        if ($last) $next = ((int)substr($last,-4)) + 1;

        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }
}
