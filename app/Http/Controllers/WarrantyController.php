<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Invoice;

class WarrantyController extends Controller
{
    public function index(Request $request)
    {
        $card = trim($request->get('card_number', ''));
        $customer = null;

        if ($card !== '') {
            // eager load invoice
            $customer = Customer::with('invoice')->where('card_number', $card)->first();
        }

        // Tampilkan form + (opsional) tombol "VIEW CARD"
        return view('frontend.warranty.index', [
            'card_number' => $card,
            'customer'    => $customer,
        ]);
    }

    // Tampilkan halaman kartu/invoice
    public function showInvoice(Invoice $invoice)
    {
        $customer = Customer::with([
            'products',
            'customerProducts.part',
            'customerProducts.product',
            'customerProducts.categoryProduct'
        ])->findOrFail($invoice->customer_id);
        $invoice = Invoice::with('customer')->findOrFail($invoice->id);

        return view('frontend.warranty.show', compact('invoice', 'customer'));
    }

}
