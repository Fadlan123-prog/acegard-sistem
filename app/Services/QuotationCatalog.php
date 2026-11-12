<?php

namespace App\Services;

use App\Models\CategoryProduct;
use App\Models\CategoryProductBuilding;
use App\Models\Part;
use App\Models\PpfProduct;
use App\Models\Product;
use App\Models\ProductBuilding;

/**
 * Katalog produk untuk pilihan deskripsi/spesifikasi pada baris quotation, per kategori quotation.
 * Bentuk: ['car_film' => [['group' => 'NOTCH UV 400', 'items' => [['label' => ..., 'spec' => ...]]]], ...]
 */
class QuotationCatalog
{
    public function build(): array
    {
        return [
            'car_film'      => $this->film(CategoryProduct::class, Product::class, 'category_id'),
            'building_film' => $this->film(CategoryProductBuilding::class, ProductBuilding::class, 'category_product_building_id'),
            'ppf'           => $this->ppf(),
        ];
    }

    private function film(string $categoryModel, string $productModel, string $fk): array
    {
        $products = $productModel::orderBy('name')->get()->groupBy($fk);
        $groups = [];

        foreach ($categoryModel::orderBy('name')->get() as $cat) {
            $items = [];
            foreach ($products->get($cat->id, collect()) as $p) {
                $dark = trim((string) $p->description);
                $items[] = [
                    'label' => trim($cat->name . ' — ' . $p->name),
                    'spec'  => trim(($dark !== '' ? "Kegelapan {$dark}" : '') . ($cat->duration ? ($dark !== '' ? ', ' : '') . "garansi {$cat->duration} tahun" : '')),
                ];
            }
            if ($items) {
                $groups[] = ['group' => $cat->name, 'items' => $items];
            }
        }

        return $groups;
    }

    private function ppf(): array
    {
        $items = PpfProduct::orderBy('brand')->orderBy('name')->get()->map(function ($p) {
            $spec = array_filter([
                $p->series ? "Seri {$p->series}" : null,
                $p->thickness_microns ? "{$p->thickness_microns} micron" : null,
                $p->finish ? ucfirst($p->finish) : null,
                $p->default_warranty_years ? "garansi {$p->default_warranty_years} tahun" : null,
            ]);

            return ['label' => trim($p->brand . ' ' . $p->name), 'spec' => implode(', ', $spec)];
        })->all();

        return $items ? [['group' => 'PPF', 'items' => $items]] : [];
    }

    /** Label bagian kaca yang mudah dibaca untuk pelanggan. */
    public static function partLabel(string $name): string
    {
        $n = strtolower(trim($name));

        return match (true) {
            str_contains($n, 'windshield')   => 'Kaca Depan',
            str_contains($n, 'first row')    => 'Samping Baris 1',
            str_contains($n, 'second row')   => 'Samping Baris 2',
            str_contains($n, 'third row')    => 'Samping Baris 3',
            str_contains($n, 'rear')         => 'Kaca Belakang',
            str_contains($n, 'sunroof'), str_contains($n, 'pano') => 'Sunroof / Panoramic',
            default                          => ucwords(strtolower($name)),
        };
    }

    /** Data terstruktur untuk baris quotation mobil: bagian kaca, tipe film, dan kode+kegelapan. */
    public function carStructured(): array
    {
        return [
            'parts' => Part::orderBy('id')->get()->map(fn ($p) => ['id' => $p->id, 'label' => self::partLabel($p->name)])->all(),
            'types' => CategoryProduct::orderBy('name')->get()->map(fn ($c) => [
                'id'       => $c->id,
                'name'     => $c->name,
                'products' => Product::where('category_id', $c->id)->orderBy('name')->get()
                    ->map(fn ($p) => ['id' => $p->id, 'label' => trim($p->name . ($p->description ? ' — kegelapan ' . $p->description : ''))])->all(),
            ])->all(),
        ];
    }

    /**
     * Susun deskripsi & spesifikasi paket kaca film mobil dari daftar bagian
     * (dihitung di server, bukan dipercaya dari browser). Bagian dengan tipe+kegelapan sama digabung.
     *
     * @param  array<int,array{part_id:int|string,category_id:int|string,product_id:int|string}>  $lines
     * @return array{description:string,specifications:string,lines:array}
     */
    public function composeCarPackage(array $lines, ?string $title = null): array
    {
        $parts = Part::pluck('name', 'id');
        $cats  = CategoryProduct::pluck('name', 'id');
        $prods = Product::get()->keyBy('id');

        $groups = [];
        $normalized = [];
        foreach ($lines as $l) {
            $partId = (int) $l['part_id'];
            $catId  = (int) $l['category_id'];
            $prodId = (int) $l['product_id'];
            $normalized[] = ['part_id' => $partId, 'category_id' => $catId, 'product_id' => $prodId];

            $p = $prods->get($prodId);
            $film = trim(($cats[$catId] ?? '') . ' ' . ($p->name ?? '') . ($p && $p->description ? " (kegelapan {$p->description})" : ''));
            $groups[$film][] = self::partLabel((string) ($parts[$partId] ?? ''));
        }

        $spec = collect($groups)->map(fn ($labels, $film) => implode(', ', array_unique($labels)) . ': ' . $film)->implode('; ');

        return [
            'description'    => trim((string) $title) !== '' ? trim((string) $title) : 'Paket Kaca Film Mobil',
            'specifications' => \Illuminate\Support\Str::limit($spec, 250, '…'),
            'lines'          => $normalized,
        ];
    }

    /** Aturan validasi tambahan untuk rincian paket mobil (dipakai Quotation dan Sales Order). */
    public static function packageRules(): array
    {
        return [
            'items.*.vehicle_label'            => 'nullable|string|max:120',
            'items.*.vehicle_plate'            => 'nullable|string|max:20',
            'items.*.film_lines'               => 'nullable|array',
            'items.*.film_lines.*.part_id'     => 'required|integer|exists:parts,id',
            'items.*.film_lines.*.category_id' => 'required|integer|exists:category_products,id',
            'items.*.film_lines.*.product_id'  => 'required|integer|exists:products,id',
        ];
    }

    /**
     * Normalisasi baris dokumen setelah validasi: untuk kategori mobil dengan film_lines, deskripsi,
     * spesifikasi, satuan (paket), qty (1), dan diskon baris (0) disusun/dipaksa server; kategori lain
     * membuang data paket & kendaraan. Melempar ValidationException bila kegelapan bukan milik tipe film.
     */
    public function normalizeItems(array $items, ?string $category): array
    {
        foreach ($items as $i => $item) {
            $lines = array_values(array_filter((array) ($item['film_lines'] ?? [])));

            if ($category !== 'car_film' || ! $lines) {
                $items[$i]['film_lines'] = null;
                $items[$i]['vehicle_label'] = $items[$i]['vehicle_plate'] = null;
                continue;
            }

            $items[$i]['vehicle_label'] = trim((string) ($item['vehicle_label'] ?? '')) ?: null;
            $items[$i]['vehicle_plate'] = strtoupper(trim((string) ($item['vehicle_plate'] ?? ''))) ?: null;

            foreach ($lines as $j => $l) {
                if (! Product::where('id', (int) $l['product_id'])->where('category_id', (int) $l['category_id'])->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "items.$i.film_lines.$j.product_id" => 'Persentase kegelapan tidak sesuai dengan tipe film yang dipilih.',
                    ]);
                }
            }

            $package = $this->composeCarPackage($lines, $item['description'] ?? null);
            $items[$i]['film_lines']       = $package['lines'];
            $items[$i]['description']      = $package['description'];
            $items[$i]['specifications']   = $package['specifications'];
            $items[$i]['unit']             = 'paket';
            $items[$i]['quantity']         = 1;
            $items[$i]['discount_percent'] = 0;
        }

        return $items;
    }
}
