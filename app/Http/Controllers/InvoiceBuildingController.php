<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerBuilding;
use App\Models\InvoiceBuilding;
use App\Models\CustomerBuildingProduct;
use Illuminate\Support\Facades\DB;
use PDF;

class InvoiceBuildingController extends Controller
{
    public function create(CustomerBuilding $customer)
    {
        $customerProducts = $customer->products()->with('product', 'category')->get();

        return view('invoice.building.create', [
            'customer' => $customer,
            'customerProducts' => $customerProducts
        ]);
    }
    public function store(Request $request){
        $request->validate([
        'customer_building_id' => 'required|exists:customer_building,id',
        'branch_id' => 'required|exists:branches,id',
        'user_id' => 'required|exists:users,id',
        'invoice_date' => 'required|date',
        'type' => 'required|string',
        'products' => 'required|array',
        'products.*.customer_building_product_id' => 'required|exists:customer_building_product,id',
        'products.*.price' => 'required|integer|min:0',
        'discount' => 'nullable|integer|min:0',
        'down_payment' => 'nullable|integer|min:0',
    ]);

    DB::beginTransaction();
    try {
        // Hitung total harga
        $totalPrice = collect($request->products)->sum('price');

        $invoice = InvoiceBuilding::create([
            'customer_building_id' => $request->customer_building_id,
            'branch_id' => $request->branch_id,
            'user_id' => $request->user_id,
            'type' => $request->type,
            'price' => $totalPrice,
            'discount' => $request->discount ?? 0,
            'down_payment' => $request->down_payment ?? 0,
            'total_price' => $totalPrice - ($request->discount ?? 0),
            'remaining_payment' => ($totalPrice - ($request->discount ?? 0)) - ($request->downpayment ?? 0),
            'status' => 'draft',
            'invoice_number' => 'INV-' . now()->format('YmdHis'),
            'invoice_date' => $request->invoice_date,
        ]);

        foreach ($request->products as $item) {
            $invoice->products()->attach($item['customer_building_product_id'], [
                'price' => $item['price']
            ]);
        }

        DB::commit();
        return redirect()->route('customer.building.index')->with('success', 'Invoice berhasil dibuat.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Gagal menyimpan invoice: ' . $e->getMessage());
    }
    }

    public function show(InvoiceBuilding $invoice)
    {
        $customer = CustomerBuilding::with([
            'products',
            'products.product',
            'products.category',
            'products.categoryProductBuilding'
        ])->findOrFail($invoice->customer_building_id);
        $invoice = InvoiceBuilding::with('customer')->findOrFail($invoice->id);
        return view('invoice.building.show', compact('invoice', 'customer'));
    }

    public function edit($id)
    {
        $invoice = InvoiceBuilding::with([
            'products', // relasi pivot
            'products.product',
            'products.category',
            'customer' // agar bisa ambil data customer
        ])->findOrFail($id);

        $customer = $invoice->customer;

        // Ambil semua product milik customer ini
        $customerProducts = CustomerBuildingProduct::with(['product', 'category'])
            ->where('customer_building_id', $customer->id)
            ->get();

        // Inject harga (pivot price) dari invoice ke dalam collection customerProducts
        foreach ($customerProducts as $prod) {
            $pivot = $invoice->products->firstWhere('id', $prod->id);
            if ($pivot) {
                $prod->pivot = $pivot->pivot; // inject pivot jika ada
            }
        }

        return view('invoice.building.edit', compact('invoice', 'customer', 'customerProducts'));
    }


    public function update(Request $request, $id)
{
    $request->validate([
        'invoice_number' => 'required',
        'invoice_date' => 'required|date',
        'type' => 'required|in:cash,credit',
        'discount' => 'nullable|numeric',
        'down_payment' => 'nullable|numeric',
        'status' => 'required|in:unpaid,partial,paid',
        'products' => 'required|array',
        'products.*.customer_building_product_id' => 'required|exists:customer_building_product,id',
        'products.*.price' => 'required|numeric',
    ]);

    try {
        DB::beginTransaction();

        $invoice = InvoiceBuilding::findOrFail($id);
        $invoice->update([
            'invoice_number' => $request->invoice_number,
            'invoice_date' => $request->invoice_date,
            'type' => $request->type,
            'discount' => $request->discount,
            'downpayment' => $request->down_payment,
            'status' => $request->status,
        ]);

        // Sync ulang produk dengan harga
        $syncData = [];
        foreach ($request->products as $prod) {
            $syncData[$prod['customer_building_product_id']] = ['price' => $prod['price']];
        }

        $invoice->products()->sync($syncData);

        DB::commit();
        return redirect()->route('invoice.building.index')->with('success', 'Invoice berhasil diperbarui.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Gagal mengupdate invoice: ' . $e->getMessage())->withInput();
    }
}

public function download(InvoiceBuilding $invoice)
    {
        $customer = CustomerBuilding::with(['products.product', 'products.category'])
            ->findOrFail($invoice->customer_building_id);

        $customerProduct = $customer->products;

        $pdf = PDF::loadView('invoice.building.pdf', compact('invoice', 'customer', 'customerProduct'));
        return $pdf->stream();
    }
}
