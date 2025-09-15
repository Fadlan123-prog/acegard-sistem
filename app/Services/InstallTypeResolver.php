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
        \DateTimeInterface|string|null $forDate = null
    ): string {
        $date = $forDate ? Carbon::parse($forDate)->toDateString() : Carbon::today()->toDateString();
        if ($baseType === 'skkb') return 'skkb';
        if ($baseType === 'dsp')  return 'dsp';

        $countToday = DB::table('customers')
            ->where('tukang_id', $tukangId)
            ->where('install_type', 'fullset')
            ->whereDate('created_at', $date)
            ->count();

        if ($hasPanoramic) {
            return match (true) {
                $countToday === 0 => 'full_panoramic',
                $countToday === 1 => 'ekstra_panoramic',
                default           => 'fullset_ekstra_plus',
            };
        }
        return match (true) {
            $countToday === 0 => 'fullset',
            $countToday === 1 => 'fullset_ekstra',
            default           => 'fullset_ekstra_plus',
        };
    }

    // >>> Tambahan ini <<<
    public function decideAfterInsert(
        int $tukangId,
        string $baseType,
        bool $hasPanoramic = false,
        ?int $excludeCustomerId = null,
        \DateTimeInterface|string|null $forDate = null
    ): string {
        $date = $forDate ? Carbon::parse($forDate)->toDateString() : Carbon::today()->toDateString();
        if ($baseType === 'skkb') return 'skkb';
        if ($baseType === 'dsp')  return 'dsp';

        $q = DB::table('customers')
            ->where('tukang_id', $tukangId)
            ->where('install_type', 'fullset')
            ->whereDate('created_at', $date);

        if ($excludeCustomerId) {
            $q->where('id', '!=', $excludeCustomerId);
        }

        $countToday = $q->count();

        if ($hasPanoramic) {
            return match (true) {
                $countToday === 0 => 'full_panoramic',
                $countToday === 1 => 'ekstra_panoramic',
                default           => 'fullset_ekstra_plus',
            };
        }
        return match (true) {
            $countToday === 0 => 'fullset',
            $countToday === 1 => 'fullset_ekstra',
            default           => 'fullset_ekstra_plus',
        };
    }
}
