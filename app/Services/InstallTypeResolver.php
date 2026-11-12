<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InstallTypeResolver
{
    /**
     * Tentukan tipe komisi final berdasarkan:
     * - baseType: 'fullset' | 'skkb' | 'dsp'
     * - hasPanoramic: true/false
     * - tanggal (default: hari ini)
     * Hitung jumlah pekerjaan "Fullset" si tukang pada tanggal tsb dari tabel customers.
     */
    public function decideFromBase(
        int $tukangId,
        string $baseType,
        bool $hasPanoramic = false,
        \DateTimeInterface|string|null $forDate = null,
        ?array $partIds = null
    ): string {
        $date = $forDate ? Carbon::parse($forDate)->toDateString() : Carbon::today()->toDateString();
        if ($baseType === 'skkb') return $hasPanoramic ? 'skkb_panoramic' : 'skkb';
        if ($baseType === 'dsp')  return ($hasPanoramic && $this->dspDouble($partIds)) ? 'dsp_panoramic' : 'dsp';

        $countToday = DB::table('customers')
            ->where('tukang_id', $tukangId)
            ->where('install_type', 'fullset')
            ->whereRaw('COALESCE(install_date, DATE(created_at)) = ?', [$date])
            ->count();

        if ($hasPanoramic) {
            return match (true) {
                $countToday === 0 => 'full_panoramic',
                $countToday === 1 => 'ekstra_panoramic',
                default           => 'fullset_ekstra_plus_panoramic',
            };
        }
        return match (true) {
            $countToday === 0 => 'fullset',
            $countToday === 1 => 'fullset_ekstra',
            default           => 'fullset_ekstra_plus',
        };
    }

    public function decideAfterInsert(
        int $tukangId,
        string $baseType,
        bool $hasPanoramic = false,
        ?int $excludeCustomerId = null,
        \DateTimeInterface|string|null $forDate = null,
        ?array $partIds = null
    ): string {
        $date = $forDate ? Carbon::parse($forDate)->toDateString() : Carbon::today()->toDateString();
        if ($baseType === 'skkb') return $hasPanoramic ? 'skkb_panoramic' : 'skkb';
        if ($baseType === 'dsp')  return ($hasPanoramic && $this->dspDouble($partIds)) ? 'dsp_panoramic' : 'dsp';

        $q = DB::table('customers')
            ->where('tukang_id', $tukangId)
            ->where('install_type', 'fullset')
            ->whereRaw('COALESCE(install_date, DATE(created_at)) = ?', [$date]);

        if ($excludeCustomerId) {
            // Count only customers entered BEFORE this one (lower id).
            // Using != caused both cars to see each other → both upgraded.
            $q->where('id', '<', $excludeCustomerId);
        }

        $countToday = $q->count();

        if ($hasPanoramic) {
            return match (true) {
                $countToday === 0 => 'full_panoramic',
                $countToday === 1 => 'ekstra_panoramic',
                default           => 'fullset_ekstra_plus_panoramic',
            };
        }
        return match (true) {
            $countToday === 0 => 'fullset',
            $countToday === 1 => 'fullset_ekstra',
            default           => 'fullset_ekstra_plus',
        };
    }

    /** D/S/P dihitung 2× hanya bila bagian yang dipasang persis kaca depan + panoramic. */
    private function dspDouble(?array $partIds): bool
    {
        if ($partIds === null) {
            return true; // pemanggil tidak memberi daftar bagian: perilaku lama
        }

        $names = DB::table('parts')->whereIn('id', array_filter($partIds))->pluck('name')->all();

        return \App\Domain\Commission\CommissionCalculator::isWindshieldPlusPanoramicOnly($names);
    }
}
