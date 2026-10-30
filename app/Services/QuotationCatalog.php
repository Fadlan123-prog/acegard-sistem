<?php

namespace App\Services;

use App\Models\CategoryProduct;
use App\Models\CategoryProductBuilding;
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
}
