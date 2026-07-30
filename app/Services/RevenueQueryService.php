<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for cross-table revenue aggregations.
 *
 * Extracted from the 230-line duplication between:
 *   - DashboardController::monthlyData/yearlyData/summary/availableYears
 *   - StatisticsController::monthlyData/yearlyData/summary/availableYears
 *
 * Both controllers now inject this service instead of carrying their own copies.
 *
 * All methods accept an optional ?int $branchId. When null, results are global
 * (superadmin cross-branch view). When set, results are scoped to that branch.
 */
final class RevenueQueryService
{
    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * All years that have at least one invoice across all three invoice types.
     * Includes the current year even if it has no invoices yet.
     */
    public function availableYears(): Collection
    {
        $car      = DB::table('invoices')
            ->selectRaw('YEAR(invoice_date) as y')
            ->whereNotNull('invoice_date')
            ->distinct()->pluck('y');

        $building = DB::table('invoice_building')
            ->selectRaw('YEAR(invoice_date) as y')
            ->whereNotNull('invoice_date')
            ->distinct()->pluck('y');

        $ppf = DB::table('ppf_invoices')
            ->selectRaw('YEAR(invoice_date) as y')
            ->whereNotNull('invoice_date')
            ->distinct()->pluck('y');

        return $car->merge($building)->merge($ppf)->merge([now()->year])
            ->unique()->filter()->sortDesc()->values();
    }

    /**
     * Per-month revenue breakdown for chart rendering.
     *
     * Returns an array keyed 1–12 (month number). Each entry:
     *   car_count, car_amount, building_count, building_amount, ppf_count, ppf_amount
     *
     * All 12 months are always present, zero-filled for months without data.
     */
    public function monthly(int $year, ?int $branchId): array
    {
        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $data[$m] = [
                'car_count'        => 0,
                'car_amount'       => 0,
                'building_count'   => 0,
                'building_amount'  => 0,
                'ppf_count'        => 0,
                'ppf_amount'       => 0,
            ];
        }

        // Car invoices — joined through customers for branch scoping
        $carQ = DB::table('invoices')
            ->join('customers', 'customers.id', '=', 'invoices.customer_id')
            ->selectRaw('MONTH(invoices.invoice_date) as m, COUNT(*) as cnt, COALESCE(SUM(invoices.total_price),0) as amt')
            ->whereYear('invoices.invoice_date', $year)
            ->whereNotIn('invoices.status', ['void']);
        if ($branchId) $carQ->where('customers.branch_id', $branchId);
        foreach ($carQ->groupByRaw('MONTH(invoices.invoice_date)')->get() as $r) {
            $data[(int) $r->m]['car_count']  = (int)   $r->cnt;
            $data[(int) $r->m]['car_amount'] = (float) $r->amt;
        }

        // Building invoices — branch_id is a direct column
        $bldQ = DB::table('invoice_building')
            ->selectRaw('MONTH(invoice_date) as m, COUNT(*) as cnt, COALESCE(SUM(total_price),0) as amt')
            ->whereYear('invoice_date', $year)
            ->whereNotNull('invoice_date');
        if ($branchId) $bldQ->where('branch_id', $branchId);
        foreach ($bldQ->groupByRaw('MONTH(invoice_date)')->get() as $r) {
            $data[(int) $r->m]['building_count']  = (int)   $r->cnt;
            $data[(int) $r->m]['building_amount'] = (float) $r->amt;
        }

        // PPF invoices
        $ppfQ = DB::table('ppf_invoices')
            ->selectRaw('MONTH(invoice_date) as m, COUNT(*) as cnt, COALESCE(SUM(total_price),0) as amt')
            ->whereYear('invoice_date', $year)
            ->where('status', '!=', 'void');
        if ($branchId) $ppfQ->where('branch_id', $branchId);
        foreach ($ppfQ->groupByRaw('MONTH(invoice_date)')->get() as $r) {
            $data[(int) $r->m]['ppf_count']  = (int)   $r->cnt;
            $data[(int) $r->m]['ppf_amount'] = (float) $r->amt;
        }

        return $data;
    }

    /**
     * Per-year revenue breakdown for trend charts.
     *
     * Returns an array keyed by year (int). Each entry:
     *   car_count, car_amount, building_count, building_amount, ppf_count, ppf_amount,
     *   total_count, total_amount
     */
    public function yearly(?int $branchId): array
    {
        $blank = [
            'car_count'       => 0,
            'car_amount'      => 0,
            'building_count'  => 0,
            'building_amount' => 0,
            'ppf_count'       => 0,
            'ppf_amount'      => 0,
        ];

        $data = [];

        // Car
        $carQ = DB::table('invoices')
            ->join('customers', 'customers.id', '=', 'invoices.customer_id')
            ->selectRaw('YEAR(invoices.invoice_date) as yr, COUNT(*) as cnt, COALESCE(SUM(invoices.total_price),0) as amt')
            ->whereNotNull('invoices.invoice_date')
            ->whereNotIn('invoices.status', ['void']);
        if ($branchId) $carQ->where('customers.branch_id', $branchId);
        foreach ($carQ->groupByRaw('YEAR(invoices.invoice_date)')->get() as $r) {
            $y = (int) $r->yr;
            $data[$y]['car_count']  = (int)   $r->cnt;
            $data[$y]['car_amount'] = (float) $r->amt;
        }

        // Building
        $bldQ = DB::table('invoice_building')
            ->selectRaw('YEAR(invoice_date) as yr, COUNT(*) as cnt, COALESCE(SUM(total_price),0) as amt')
            ->whereNotNull('invoice_date');
        if ($branchId) $bldQ->where('branch_id', $branchId);
        foreach ($bldQ->groupByRaw('YEAR(invoice_date)')->get() as $r) {
            $y = (int) $r->yr;
            $data[$y]['building_count']  = (int)   $r->cnt;
            $data[$y]['building_amount'] = (float) $r->amt;
        }

        // PPF
        $ppfQ = DB::table('ppf_invoices')
            ->selectRaw('YEAR(invoice_date) as yr, COUNT(*) as cnt, COALESCE(SUM(total_price),0) as amt')
            ->whereNotNull('invoice_date')
            ->where('status', '!=', 'void');
        if ($branchId) $ppfQ->where('branch_id', $branchId);
        foreach ($ppfQ->groupByRaw('YEAR(invoice_date)')->get() as $r) {
            $y = (int) $r->yr;
            $data[$y]['ppf_count']  = (int)   $r->cnt;
            $data[$y]['ppf_amount'] = (float) $r->amt;
        }

        ksort($data);

        foreach ($data as &$d) {
            $d = array_merge($blank, $d);
            $d['total_count']  = $d['car_count']  + $d['building_count']  + $d['ppf_count'];
            $d['total_amount'] = $d['car_amount'] + $d['building_amount'] + $d['ppf_amount'];
            // Alias 'total' for DashboardController backward-compat
            $d['total']        = $d['total_amount'];
        }

        return $data;
    }

    /**
     * Summary metrics for a given year and optionally a branch.
     *
     * Returns: ['car' => [...], 'building' => [...], 'ppf' => [...]]
     * Each inner array has keys: year(count/total/paid), all(count/total/paid)
     */
    public function summary(int $year, ?int $branchId): array
    {
        // "paid" = money actually collected: total minus what's still outstanding.
        // Using total_price - remaining_payment captures partial payments (DP paid,
        // final not yet received) that status='paid' alone would miss.
        // COALESCE(remaining_payment, total_price) treats NULL remaining as "nothing collected".
        $cols = fn (string $t) =>
            "COUNT(*) as cnt, "
            . "COALESCE(SUM({$t}.total_price),0) as total, "
            . "COALESCE(SUM({$t}.total_price - COALESCE({$t}.remaining_payment, {$t}.total_price)),0) as paid";

        $parse = fn ($r) => [
            'count' => (int)   ($r->cnt   ?? 0),
            'total' => (float) ($r->total ?? 0),
            'paid'  => (float) ($r->paid  ?? 0),
        ];

        // Car
        $carBase = DB::table('invoices')
            ->join('customers', 'customers.id', '=', 'invoices.customer_id')
            ->whereNotIn('invoices.status', ['void']);
        if ($branchId) $carBase->where('customers.branch_id', $branchId);
        $car = [
            'year' => $parse((clone $carBase)->whereYear('invoices.invoice_date', $year)->selectRaw($cols('invoices'))->first()),
            'all'  => $parse((clone $carBase)->selectRaw($cols('invoices'))->first()),
        ];

        // Building
        $bldBase = DB::table('invoice_building')->whereNotNull('invoice_date');
        if ($branchId) $bldBase->where('branch_id', $branchId);
        $building = [
            'year' => $parse((clone $bldBase)->whereYear('invoice_date', $year)->selectRaw($cols('invoice_building'))->first()),
            'all'  => $parse((clone $bldBase)->selectRaw($cols('invoice_building'))->first()),
        ];

        // PPF
        $ppfBase = DB::table('ppf_invoices')->where('status', '!=', 'void');
        if ($branchId) $ppfBase->where('branch_id', $branchId);
        $ppf = [
            'year' => $parse((clone $ppfBase)->whereYear('invoice_date', $year)->selectRaw($cols('ppf_invoices'))->first()),
            'all'  => $parse((clone $ppfBase)->selectRaw($cols('ppf_invoices'))->first()),
        ];

        return compact('car', 'building', 'ppf');
    }
}
