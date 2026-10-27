<?php

namespace App\Http\Controllers;

use App\Models\SalesProduct;
use Illuminate\Http\Request;

class SalesProductController extends Controller
{
    public function index()
    {
        $products = SalesProduct::orderBy('category')->orderBy('name')->get();

        return view('sales-products.index', compact('products'));
    }

    public function create()
    {
        return view('sales-products.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request);

        SalesProduct::create($data);

        return redirect()->route('sales-products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(SalesProduct $salesProduct)
    {
        return view('sales-products.edit', ['product' => $salesProduct]);
    }

    public function update(Request $request, SalesProduct $salesProduct)
    {
        $data = $this->validateProduct($request);

        $salesProduct->update($data);

        return redirect()->route('sales-products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function toggle(SalesProduct $salesProduct)
    {
        $salesProduct->update(['is_active' => ! $salesProduct->is_active]);

        return back()->with('success', 'Status produk berhasil diubah.');
    }

    private function validateProduct(Request $request): array
    {
        return $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:50'],
            'price'    => ['required', 'integer', 'min:0'],
            'is_active'=> ['nullable', 'boolean'],
        ]);
    }
}
