<?php

namespace App\Http\Controllers;

use App\Models\BranchRollPrice;
use App\Models\CategoryProduct;
use App\Models\CategoryProductBuilding;
use Illuminate\Http\Request;

/** Daftar harga jual roll antar cabang, satu harga per kategori film. */
class BranchRollPriceController extends Controller
{
    public function index()
    {
        $car = CategoryProduct::orderBy('name')->get(['id', 'name']);
        $building = CategoryProductBuilding::whereNotNull('name')->orderBy('name')->get(['id', 'name']);

        return view('branch-roll-prices.index', [
            'car'           => $car,
            'building'      => $building,
            'carPrices'     => BranchRollPrice::whereNotNull('category_product_id')->pluck('price', 'category_product_id'),
            'buildingPrices' => BranchRollPrice::whereNotNull('category_product_building_id')->pluck('price', 'category_product_building_id'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'car'        => ['nullable', 'array'],
            'car.*'      => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'building'   => ['nullable', 'array'],
            'building.*' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
        ]);

        foreach (['car' => 'category_product_id', 'building' => 'category_product_building_id'] as $key => $column) {
            foreach (($data[$key] ?? []) as $categoryId => $price) {
                if ($price === null || $price === '') {
                    BranchRollPrice::where($column, $categoryId)->delete();
                    continue;
                }

                BranchRollPrice::updateOrCreate([$column => (int) $categoryId], ['price' => $price, 'unit' => 'roll']);
            }
        }

        return back()->with('success', 'Harga roll antar cabang disimpan.');
    }
}
