<?php

namespace App\Http\Controllers;

use App\Models\CategoryProduct;
use App\Models\CategoryProductBuilding;
use App\Models\Branch;
use App\Models\FilmRollPrice;
use Illuminate\Http\Request;

/**
 * Daftar harga jual roll: satu daftar per nama film (mobil dan gedung sama), dua kolom
 * menurut kategori pembeli (franchise / non-franchise), dipakai Invoice Roll Antar Cabang.
 */
class BranchRollPriceController extends Controller
{
    public function index()
    {
        $carNames = CategoryProduct::whereNotNull('name')->pluck('name')
            ->map(fn ($n) => strtoupper(trim($n)))->all();

        $films = CategoryProductBuilding::whereNotNull('name')->orderBy('name')->get(['id', 'name']);
        $prices = FilmRollPrice::whereIn('category_product_building_id', $films->pluck('id'))
            ->get()->keyBy('category_product_building_id');

        return view('branch-roll-prices.index', [
            'films'      => $films,
            'prices'     => $prices,
            'carNames'   => $carNames,
            'ownerships' => Branch::OWNERSHIP_TYPES,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'prices'                 => ['nullable', 'array'],
            'prices.*'               => ['nullable', 'array'],
            'prices.*.franchise'     => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'prices.*.non_franchise' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
        ]);

        $valid = CategoryProductBuilding::whereNotNull('name')->pluck('id')->all();

        foreach (($data['prices'] ?? []) as $categoryId => $row) {
            if (!in_array((int) $categoryId, $valid, true)) {
                continue;
            }

            // Kosong = 0 (belum diatur). Baris tidak dihapus karena dirujuk item Invoice Mitra lama.
            FilmRollPrice::updateOrCreate(
                ['category_product_building_id' => (int) $categoryId],
                [
                    'price_franchise'     => $row['franchise'] ?? 0,
                    'price_non_franchise' => $row['non_franchise'] ?? 0,
                ]
            );
        }

        return back()->with('success', 'Harga roll disimpan.');
    }
}
