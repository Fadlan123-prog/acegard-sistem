<?php

namespace App\Services;

use App\Models\CategoryProduct;
use App\Models\CategoryProductBuilding;
use App\Models\Customer;
use App\Models\CustomerBuilding;
use App\Models\FilmPartUsageEstimate;
use App\Models\FilmRollEntry;
use App\Models\FilmStockPool;
use App\Models\FilmStockUsage;
use App\Models\Product;
use App\Models\ProductBuilding;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FilmStockPoolService
{
    public function addRolls(array $data): FilmStockPool
    {
        return DB::transaction(function () use ($data) {
            $width  = (float) ($data['width_meter'] ?? 1.5);
            $length = (float) ($data['length_meter'] ?? 30);
            $qty    = (int) ($data['qty_roll'] ?? 1);

            $totalLength = $length * $qty;
            $identity = $this->resolveIdentity($data);

            $pool = FilmStockPool::firstOrCreate(
                [
                    'branch_id' => (int) $data['branch_id'],

                    'category_product_id' => $data['category_product_id'] ?? null,
                    'product_id' => $data['product_id'] ?? null,

                    'category_product_building_id' => $data['category_product_building_id'] ?? null,
                    'product_building_id' => $data['product_building_id'] ?? null,

                    'usage_for' => $data['usage_for'] ?? 'universal',
                ],
                [
                    'film_type' => $identity['film_type'],
                    'darkness_code' => $identity['darkness_code'],
                    'width_meter' => $width,
                    'total_received_length_meter' => 0,
                    'total_used_length_meter' => 0,
                    'remaining_length_meter' => 0,
                    'remaining_area_meter' => 0,
                ]
            );

            $pool->film_type = $identity['film_type'];
            $pool->darkness_code = $identity['darkness_code'];
            $pool->width_meter = $width;

            $pool->total_received_length_meter = (float) $pool->total_received_length_meter + $totalLength;
            $pool->remaining_length_meter = (float) $pool->remaining_length_meter + $totalLength;
            $pool->remaining_area_meter = (float) $pool->remaining_length_meter * $width;
            $pool->save();

            foreach (($data['roll_codes'] ?? []) as $rollCode) {
                FilmRollEntry::create([
                    'film_stock_pool_id' => $pool->id,
                    'branch_id' => (int) $data['branch_id'],
                    'current_branch_id' => (int) $data['branch_id'],

                    'roll_code' => $rollCode,
                    'film_type' => $identity['film_type'],
                    'darkness_code' => $identity['darkness_code'],

                    'width_meter' => $width,
                    'length_meter' => $length,
                    'area_meter' => $width * $length,

                    'status' => 'in_warehouse',

                    'received_date' => $data['received_date'] ?? now()->toDateString(),
                    'notes' => $data['notes'] ?? null,
                ]);
            }

            return $pool;
        });
    }

    protected function resolveIdentity(array $data): array
    {
        if (!empty($data['category_product_id']) && !empty($data['product_id'])) {
            $category = CategoryProduct::findOrFail((int) $data['category_product_id']);
            $product = Product::findOrFail((int) $data['product_id']);

            return [
                'film_type' => $category->name,
                'darkness_code' => $product->name,
            ];
        }

        if (!empty($data['category_product_building_id']) && !empty($data['product_building_id'])) {
            $category = CategoryProductBuilding::findOrFail((int) $data['category_product_building_id']);
            $product = ProductBuilding::findOrFail((int) $data['product_building_id']);

            return [
                'film_type' => $category->name,
                'darkness_code' => $product->name,
            ];
        }

        throw ValidationException::withMessages([
            'product_id' => 'Kategori dan produk wajib dipilih.',
        ]);
    }

    public function consumeFromPool(array $criteria, float $usedLength, array $usageData): void
    {
        DB::transaction(function () use ($criteria, $usedLength, $usageData) {
            $identity = $this->resolveIdentityFromCriteria($criteria);

            $branchId = (int) $criteria['branch_id'];
            $usageFor = $criteria['usage_for'] ?? 'universal';
            $usedLength = round((float) $usedLength, 2);

            if ($usedLength <= 0) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Ambil semua pool yang cocok
            |--------------------------------------------------------------------------
            | Mobil    = car + universal
            | Gedung   = building + universal
            |
            | Jangan pakai first(), karena kalau stok car kecil tapi universal besar,
            | sistem akan salah membaca stok tidak cukup.
            */
            $pools = FilmStockPool::query()
                ->where('branch_id', $branchId)
                ->whereIn('usage_for', [$usageFor, 'universal'])
                ->whereRaw('LOWER(TRIM(film_type)) = ?', [
                    strtolower(trim($identity['film_type'])),
                ])
                ->whereRaw('LOWER(TRIM(darkness_code)) = ?', [
                    strtolower(trim($identity['darkness_code'])),
                ])
                ->where('remaining_length_meter', '>', 0)
                ->orderByRaw(
                    "CASE
                        WHEN usage_for = ? THEN 0
                        WHEN usage_for = 'universal' THEN 1
                        ELSE 2
                    END",
                    [$usageFor]
                )
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            if ($pools->isEmpty()) {
                throw ValidationException::withMessages([
                    'products' =>
                        'Stock pool roll tidak ditemukan untuk produk yang dipilih. ' .
                        'Dicari: Cabang ID ' . $branchId .
                        ', Type ' . $identity['film_type'] .
                        ', Kode ' . $identity['darkness_code'] .
                        ', Untuk ' . $usageFor . ' / universal.',
                ]);
            }

            $availableLength = round((float) $pools->sum('remaining_length_meter'), 2);

            if ($availableLength < $usedLength) {
                throw ValidationException::withMessages([
                    'products' =>
                        "Stok {$identity['film_type']} {$identity['darkness_code']} tidak cukup. " .
                        "Dibutuhkan " . number_format($usedLength, 2) . " meter, " .
                        "sisa " . number_format($availableLength, 2) . " meter.",
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Kurangi stok bertahap
            |--------------------------------------------------------------------------
            | Prioritas:
            | 1. Pool sesuai usage_for, contoh car/building
            | 2. Pool universal
            */
            $remainingNeed = $usedLength;

            foreach ($pools as $pool) {
                if ($remainingNeed <= 0) {
                    break;
                }

                $before = round((float) $pool->remaining_length_meter, 2);

                if ($before <= 0) {
                    continue;
                }

                $usedFromPool = min($before, $remainingNeed);
                $usedFromPool = round($usedFromPool, 2);

                $after = round($before - $usedFromPool, 2);

                $pool->total_used_length_meter = round(
                    (float) $pool->total_used_length_meter + $usedFromPool,
                    2
                );

                $pool->remaining_length_meter = $after;
                $pool->remaining_area_meter = round(
                    $after * (float) $pool->width_meter,
                    2
                );

                $pool->save();

                FilmStockUsage::create(array_merge($usageData, [
                    'film_stock_pool_id' => $pool->id,
                    'branch_id' => $pool->branch_id,
                    'film_type' => $pool->film_type,
                    'darkness_code' => $pool->darkness_code,

                    'used_length_meter' => $usedFromPool,
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'usage_date' => now()->toDateString(),
                ]));

                $remainingNeed = round($remainingNeed - $usedFromPool, 2);
            }
        });
    }

    public function consumeForCar(Customer $customer, array $customerProducts, string $installType): void
    {
        foreach ($customerProducts as $row) {
            $customerProduct = $row['customer_product'];
            $data = $row['data'];

            $manualLength = isset($data['used_length_meter']) && $data['used_length_meter'] !== ''
                ? (float) $data['used_length_meter']
                : null;

            if ($manualLength && $manualLength > 0) {
                $usedLength = round($manualLength, 2);
                $wastePercent = 0;
                $source = 'manual';
            } else {
                [$usedLength, $wastePercent] = $this->estimateCarLength(
                    (int) $data['part_id'],
                    $installType
                );

                $source = 'estimate';
            }

            if ($usedLength <= 0) {
                // Estimasi belum dikonfigurasi untuk part ini — skip, jangan blokir pelunasan.
                Log::warning('[FilmStock] Tidak ada estimasi untuk part, stok dilewati.', [
                    'part_id'      => $data['part_id'],
                    'install_type' => $installType,
                    'customer_id'  => $customer->id,
                ]);
                continue;
            }

            $this->consumeFromPool(
                [
                    'branch_id' => $customer->branch_id,
                    'category_product_id' => (int) $data['category_product_id'],
                    'product_id' => (int) $data['product_id'],
                    'usage_for' => 'car',
                ],
                $usedLength,
                [
                    'customer_id' => $customer->id,
                    'customer_product_id' => $customerProduct->id,

                    'category_product_id' => (int) $data['category_product_id'],
                    'product_id' => (int) $data['product_id'],
                    'part_id' => (int) $data['part_id'],

                    'usage_type' => 'car',
                    'install_type' => $installType,

                    'vehicle_brand' => $customer->vehicle_brand,
                    'vehicle_model' => $customer->vehicle_model,
                    'plat_number' => $customer->plat_number,

                    'area_meter' => round($usedLength * 1.5, 2),
                    'waste_percent' => $wastePercent,
                    'usage_source' => $source,
                    'notes' => 'Pemakaian kaca film mobil.',
                ]
            );
        }
    }

    public function consumeForBuilding(CustomerBuilding $customer, array $buildingProducts, float $wastePercent = 10): void
    {
        foreach ($buildingProducts as $row) {
            $customerBuildingProduct = $row['customer_building_product'];
            $data = $row['data'];

            $areaMeter = (float) $data['meters'];

            $manualLength = isset($data['used_length_meter']) && $data['used_length_meter'] !== ''
                ? (float) $data['used_length_meter']
                : null;

            if ($manualLength && $manualLength > 0) {
                $usedLength = round($manualLength, 2);
                $source = 'manual';
            } else {
                $baseLength = $areaMeter / 1.5;
                $usedLength = round($baseLength + ($baseLength * ($wastePercent / 100)), 2);
                $source = 'estimate';
            }

            if ($usedLength <= 0) {
                throw ValidationException::withMessages([
                    'products' => 'Pemakaian kaca film gedung tidak valid.',
                ]);
            }

            $this->consumeFromPool(
                [
                    'branch_id' => $customer->branch_id,
                    'category_product_building_id' => (int) $data['category_product_building_id'],
                    'product_building_id' => (int) $data['product_building_id'],
                    'usage_for' => 'building',
                ],
                $usedLength,
                [
                    'customer_building_id' => $customer->id,
                    'customer_building_product_id' => $customerBuildingProduct->id,

                    'category_product_building_id' => (int) $data['category_product_building_id'],
                    'product_building_id' => (int) $data['product_building_id'],

                    'usage_type' => 'building',
                    'install_type' => 'building',

                    'area_meter' => $areaMeter,
                    'waste_percent' => $wastePercent,
                    'usage_source' => $source,
                    'notes' => 'Pemakaian kaca film gedung dari input ukuran m².',
                ]
            );
        }
    }

    protected function resolveIdentityFromCriteria(array $criteria): array
    {
        if (!empty($criteria['category_product_id']) && !empty($criteria['product_id'])) {
            $category = CategoryProduct::findOrFail((int) $criteria['category_product_id']);
            $product = Product::findOrFail((int) $criteria['product_id']);

            return [
                'film_type' => $category->name,
                'darkness_code' => $product->name,
            ];
        }

        if (!empty($criteria['category_product_building_id']) && !empty($criteria['product_building_id'])) {
            $category = CategoryProductBuilding::findOrFail((int) $criteria['category_product_building_id']);
            $product = ProductBuilding::findOrFail((int) $criteria['product_building_id']);

            return [
                'film_type' => $category->name,
                'darkness_code' => $product->name,
            ];
        }

        throw ValidationException::withMessages([
            'products' => 'Kategori dan produk tidak valid untuk pencarian stock roll.',
        ]);
    }

    protected function estimateCarLength(int $partId, string $installType): array
    {
        $estimate = FilmPartUsageEstimate::query()
            ->where('part_id', $partId)
            ->where(function ($q) use ($installType) {
                $q->where('install_type', $installType)
                    ->orWhereNull('install_type');
            })
            ->orderByRaw("CASE WHEN install_type = ? THEN 0 ELSE 1 END", [$installType])
            ->first();

        if (!$estimate) {
            return [0, 0];
        }

        $base = (float) $estimate->estimated_length_meter;
        $waste = (float) $estimate->waste_percent;

        $used = round($base + ($base * ($waste / 100)), 2);

        return [$used, $waste];
    }
}
