<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchRollPrice;
use App\Models\CategoryProduct;
use App\Models\CategoryProductBuilding;
use Illuminate\Http\Request;

/**
 * Daftar harga jual roll antar cabang: satu harga per NAMA film (mobil dan gedung
 * memakai daftar yang sama) dan per kategori kepemilikan cabang pembeli.
 */
class BranchRollPriceController extends Controller
{
    /** @return array<string,array{name:string,car:bool,building:bool}> kunci film => info */
    private function films(): array
    {
        $films = [];

        foreach (CategoryProduct::whereNotNull('name')->get(['name']) as $c) {
            $films[BranchRollPrice::keyFor($c->name)] = ['name' => trim($c->name), 'car' => true, 'building' => false];
        }

        foreach (CategoryProductBuilding::whereNotNull('name')->get(['name']) as $c) {
            $key = BranchRollPrice::keyFor($c->name);
            $films[$key] = ['name' => $films[$key]['name'] ?? trim($c->name), 'car' => $films[$key]['car'] ?? false, 'building' => true];
        }

        ksort($films);

        return $films;
    }

    public function index()
    {
        $prices = BranchRollPrice::all()->groupBy('ownership_type')->map(fn ($g) => $g->pluck('price', 'film_key'));

        return view('branch-roll-prices.index', [
            'films'      => $this->films(),
            'prices'     => $prices,
            'ownerships' => Branch::OWNERSHIP_TYPES,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'prices'     => ['nullable', 'array'],
            'prices.*'   => ['nullable', 'array'],
            'prices.*.*' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
        ]);

        $known = $this->films();

        foreach (($data['prices'] ?? []) as $ownership => $byFilm) {
            if (!array_key_exists($ownership, Branch::OWNERSHIP_TYPES)) {
                continue;
            }

            foreach ($byFilm as $filmKey => $price) {
                $filmKey = BranchRollPrice::keyFor($filmKey);
                if (!isset($known[$filmKey])) {
                    continue;
                }

                $where = ['ownership_type' => $ownership, 'film_key' => $filmKey];

                if ($price === null || $price === '') {
                    BranchRollPrice::where($where)->delete();
                    continue;
                }

                BranchRollPrice::updateOrCreate($where, ['price' => $price, 'unit' => 'roll']);
            }
        }

        return back()->with('success', 'Harga roll antar cabang disimpan.');
    }
}
