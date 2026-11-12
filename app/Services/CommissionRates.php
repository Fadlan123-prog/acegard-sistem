<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tarif komisi per kategori produk. Sumber utama: tabel commission_rates (diatur dari Manajemen Produk);
 * bila belum ada baris, dipakai nilai bawaan di config/commission.php supaya perilaku lama tidak berubah.
 */
class CommissionRates
{
    public const CAR_KEYS      = ['marketing_fullset' => 'Fullset', 'marketing_dsp' => 'D/S/P', 'marketing_skkb' => 'SKKB'];
    public const BUILDING_KEYS = ['technician_per_meter' => 'Teknisi pemasang', 'marketing_per_meter' => 'Marketing'];

    /** @var array<string,?int> */
    private array $memo = [];

    private function db(string $scope, int|string|null $categoryId, string $key): ?int
    {
        if ($categoryId === null || ! is_numeric($categoryId) || ! Schema::hasTable('commission_rates')) {
            return null;
        }

        $id = $scope . ':' . (int) $categoryId . ':' . $key;
        if (! array_key_exists($id, $this->memo)) {
            $v = DB::table('commission_rates')->where('scope', $scope)->where('category_id', (int) $categoryId)->where('rate_key', $key)->value('amount');
            $this->memo[$id] = $v === null ? null : (int) $v;
        }

        return $this->memo[$id];
    }

    /**
     * Komisi marketing mobil. $baseType: fullset | full_panoramic | ekstra_panoramic | dsp | dsp_panoramic | skkb.
     * Aturan panoramic: fullset+panoramic sama dengan fullset; D/S/P + panoramic = 2× D/S/P.
     */
    public function marketingCar(int|string|null $categoryId, string $baseType): int
    {
        $slot = match (true) {
            str_starts_with($baseType, 'dsp') => 'dsp',
            $baseType === 'skkb'              => 'skkb',
            default                           => 'fullset',
        };

        $fromDb = $this->db('car', $categoryId, 'marketing_' . $slot);
        if ($fromDb !== null) {
            return $fromDb * ($baseType === 'dsp_panoramic' ? 2 : 1);
        }

        $map = (array) config('commission.marketing', []);
        $catMap = $map[$categoryId] ?? ($map['default'] ?? []);

        return (int) ($catMap[$baseType] ?? 0);
    }

    public function buildingTechnicianPerMeter(int $categoryId): int
    {
        return $this->db('building', $categoryId, 'technician_per_meter')
            ?? (int) (config('commission.building_per_meter', [])[$categoryId] ?? 0);
    }

    public function buildingMarketingPerMeter(int $categoryId): int
    {
        return $this->db('building', $categoryId, 'marketing_per_meter')
            ?? (int) (config('commission.building_marketing_per_meter', [])[$categoryId] ?? 0);
    }

    /**
     * Tarif yang berlaku untuk tampilan: [key => ['label','amount','source' => 'diatur'|'bawaan'|'belum']].
     *
     * @return array<string,array{label:string,amount:int,source:string}>
     */
    public function forCategory(string $scope, int $categoryId): array
    {
        $out = [];

        if ($scope === 'car') {
            foreach (self::CAR_KEYS as $key => $label) {
                $slot = substr($key, strlen('marketing_'));
                $db = $this->db('car', $categoryId, $key);
                $cfg = (int) ((config('commission.marketing', [])[$categoryId][$slot] ?? 0));
                $out[$key] = ['label' => $label, 'amount' => $db ?? $cfg, 'source' => $db !== null ? 'diatur' : ($cfg > 0 ? 'bawaan' : 'belum')];
            }

            return $out;
        }

        foreach (self::BUILDING_KEYS as $key => $label) {
            $db = $this->db('building', $categoryId, $key);
            $cfg = $key === 'technician_per_meter'
                ? (int) (config('commission.building_per_meter', [])[$categoryId] ?? 0)
                : (int) (config('commission.building_marketing_per_meter', [])[$categoryId] ?? 0);
            $out[$key] = ['label' => $label, 'amount' => $db ?? $cfg, 'source' => $db !== null ? 'diatur' : ($cfg > 0 ? 'bawaan' : 'belum')];
        }

        return $out;
    }

    /** @param array<string,int|string> $rates key => nominal rupiah (hanya key yang dikenal untuk scope itu) */
    public function save(string $scope, int $categoryId, array $rates, ?int $userId = null): void
    {
        $allowed = $scope === 'car' ? array_keys(self::CAR_KEYS) : array_keys(self::BUILDING_KEYS);

        foreach ($allowed as $key) {
            if (! array_key_exists($key, $rates)) {
                continue;
            }

            DB::table('commission_rates')->updateOrInsert(
                ['scope' => $scope, 'category_id' => $categoryId, 'rate_key' => $key],
                ['amount' => max(0, (int) $rates[$key]), 'updated_by' => $userId, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        $this->memo = [];
    }

    /** Hapus tarif milik kategori yang dihapus. */
    public function forget(string $scope, int $categoryId): void
    {
        if (Schema::hasTable('commission_rates')) {
            DB::table('commission_rates')->where('scope', $scope)->where('category_id', $categoryId)->delete();
        }
        $this->memo = [];
    }
}
