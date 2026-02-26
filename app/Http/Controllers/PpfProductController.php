<?php

namespace App\Http\Controllers;

use App\Models\PpfProduct;
use Illuminate\Http\Request;

class PpfProductController extends Controller
{
    public function index()
    {
        $products = PpfProduct::latest()->paginate(20);

        return view('ppf.products.index', compact('products'));
    }

    public function create()
    {
        return view('ppf.products.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'brand'                 => ['required', 'string', 'max:255'],
            'series'                => ['nullable', 'string', 'max:255'],
            'thickness_microns'     => ['nullable', 'integer'],
            'finish'                => ['nullable', 'string', 'max:50'],
            'default_warranty_years'=> ['required', 'integer', 'min:1'],
            'default_coverage'      => ['nullable', 'string'],
            'default_exclusions'    => ['nullable', 'string'],
        ]);

        PpfProduct::create($data);

        return redirect()
            ->route('ppf-products.index')
            ->with('success', 'Produk PPF berhasil ditambahkan.');
    }

    public function edit(PpfProduct $ppf_product)
    {
        return view('ppf.products.edit', ['product' => $ppf_product]);
    }

    public function update(Request $request, PpfProduct $ppf_product)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'brand'                 => ['required', 'string', 'max:255'],
            'series'                => ['nullable', 'string', 'max:255'],
            'thickness_microns'     => ['nullable', 'integer'],
            'finish'                => ['nullable', 'string', 'max:50'],
            'default_warranty_years'=> ['required', 'integer', 'min:1'],
            'default_coverage'      => ['nullable', 'string'],
            'default_exclusions'    => ['nullable', 'string'],
        ]);

        $ppf_product->update($data);

        return redirect()
            ->route('ppf-products.index')
            ->with('success', 'Produk PPF berhasil diperbarui.');
    }

    public function destroy(PpfProduct $ppf_product)
    {
        $ppf_product->delete();

        return redirect()
            ->route('ppf-products.index')
            ->with('success', 'Produk PPF berhasil dihapus.');
    }
}
