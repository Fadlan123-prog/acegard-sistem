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
     * Susun deskripsi & spesifikasi baris mobil dari pilihan terstruktur (dihitung di server, bukan dipercaya dari browser).
     *
     * @param  array<int,int|string>  $partIds
     * @return array{description:?string,specifications:?string}
     */
    public function composeCarItem(array $partIds, ?int $categoryId, ?int $productId): array
    {
        $parts = Part::whereIn('id', array_map('intval', $partIds))->orderBy('id')->get()
            ->map(fn ($p) => self::partLabel($p->name))->all();

        $spec = null;
        if ($categoryId) {
            $cat = CategoryProduct::find($categoryId);
            $prod = $productId ? Product::where('id', $productId)->where('category_id', $categoryId)->first() : null;
            $spec = trim(($cat?->name ?? '') . ($prod ? ' ' . $prod->name . ($prod->description ? ' (kegelapan ' . $prod->description . ')' : '') : ''));
        }

        return [
            'description'    => $parts ? implode(', ', $parts) : null,
            'specifications' => $spec ?: null,
        ];
    }
}
