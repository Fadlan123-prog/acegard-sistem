<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Ringkasan eksekutif lintas divisi untuk dashboard owner: omzet per kategori
 * (mobil / PPF / gedung), pertumbuhan vs periode lalu, tren bulanan, serta
 * ad spend, leads dan closing. Definisi omzet SAMA dengan halaman Analytics:
 * invoice berstatus paid, dihitung per invoice_date.
 */
class ExecutiveSummaryService
{
    public function summary(int $month, int $year, ?int $branchId = null, int $trendMonths = 6): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();
        $prevStart = $start->copy()->subMonth()->startOfMonth();
        $prevEnd   = $prevStart->copy()->endOfMonth();

        $cur  = $this->revenue($start, $end, $branchId);
        $prev = $this->revenue($prevStart, $prevEnd, $branchId);

        $trend = [];
        for ($i = $trendMonths - 1; $i >= 0; $i--) {
            $s = $start->copy()->subMonths($i)->startOfMonth();
            $trend[] = [
                'label' => $s->translatedFormat('M Y'),
                'total' => $i === 0 ? $cur['total'] : $this->revenue($s, $s->copy()->endOfMonth(), $branchId)['total'],
            ];
        }

        $spend = (float) DB::table('ad_spend_entries')
            ->whereBetween('spend_date', [$start->toDateString(), $end->toDateString()])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('amount');

        $leadQ = fn () => DB::table('leads')
            ->whereBetween('created_at', [$start, $end])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
        $leads = $leadQ()->count();
        $won   = $leadQ()->where('status', 'won')->count();

        return [
            'start' => $start,
            'end' => $end,
            'revenue' => $cur,
            'prev_total' => $prev['total'],
            'growth_pct' => $prev['total'] > 0 ? round(($cur['total'] - $prev['total']) / $prev['total'] * 100, 1) : null,
            'trend' => $trend,
            'ad_spend' => $spend,
            'leads' => $leads,
            'won_leads' => $won,
            'closing_rate' => $leads > 0 ? round($won / $leads * 100, 1) : 0,
            'cpl' => $leads > 0 && $spend > 0 ? $spend / $leads : null,
            'roas' => $spend > 0 ? round($cur['total'] / $spend, 1) : null,
        ];
    }

    /** @return array{total:int,count:int,by_category:array<string,int>} */
    private function revenue(Carbon $start, Carbon $end, ?int $branchId): array
    {
        $range = [$start->toDateString(), $end->toDateString()];

        $car = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->where('invoices.status', 'paid')
            ->whereBetween('invoices.invoice_date', $range)
            ->when($branchId, fn ($q) => $q->where('customers.branch_id', $branchId));

        $ppf = DB::table('ppf_invoices')->where('status', 'paid')
            ->whereBetween('invoice_date', $range)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $bld = DB::table('invoice_building')->where('status', 'paid')
            ->whereBetween('invoice_date', $range)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $by = [
            'Mobil'  => (int) (clone $car)->sum('invoices.total_price'),
            'PPF'    => (int) (clone $ppf)->sum('total_price'),
            'Gedung' => (int) (clone $bld)->sum('total_price'),
        ];

        return [
            'total' => array_sum($by),
            'count' => (clone $car)->count() + (clone $ppf)->count() + (clone $bld)->count(),
            'by_category' => $by,
        ];
    }
}
