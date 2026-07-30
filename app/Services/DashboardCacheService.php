<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\Customer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\RevenueQueryService;

/**
 * Branch-aware caching layer for dashboard aggregations.
 *
 * Previously, DashboardController ran 6+ raw DB queries on every page load
 * with no caching. On a branch with 1,000+ invoices, this adds 300–600ms
 * of uncached aggregation to every dashboard view.
 *
 * TTL: 15 minutes. Invalidated explicitly on invoice/customer creation.
 *
 * Usage in DashboardController:
 *   public function __construct(
 *       private BranchContext       $branch,
 *       private DashboardCacheService $cache,
 *   ) {}
 *
 *   $summary = $this->cache->getSummary($this->branch->id(), $year);
 *
 * Cache invalidation (call from InvoiceController::store and CustomerController::store):
 *   app(DashboardCacheService::class)->invalidate($branchId);
 */
final class DashboardCacheService
{
    private const TTL_MINUTES  = 15;
    private const TTL_TEKNISI  = 5;

    public function __construct(private RevenueQueryService $revenue) {}

    // ── Public API ────────────────────────────────────────────────

    /**
     * Aggregated revenue/invoice counts for the branch in the given year.
     * Keys: total_invoices, total_revenue, total_dp, total_outstanding
     */
    public function getSummary(int $branchId, int $year): array
    {
        return Cache::remember(
            "dashboard.summary.{$branchId}.{$year}",
            now()->addMinutes(self::TTL_MINUTES),
            fn () => $this->computeSummary($branchId, $year)
        );
    }

    /**
     * Per-month revenue breakdown for chart rendering.
     * Returns an array keyed by month number (1–12).
     * Keys per entry: month, revenue, invoice_count
     */
    public function getMonthlyRevenue(int $branchId, int $year): array
    {
        return Cache::remember(
            "dashboard.monthly.{$branchId}.{$year}",
            now()->addMinutes(self::TTL_MINUTES),
            fn () => $this->computeMonthlyRevenue($branchId, $year)
        );
    }

    /**
     * Building invoice revenue breakdown — separate from car invoices.
     */
    public function getBuildingSummary(int $branchId, int $year): array
    {
        return Cache::remember(
            "dashboard.building.{$branchId}.{$year}",
            now()->addMinutes(self::TTL_MINUTES),
            fn () => $this->computeBuildingSummary($branchId, $year)
        );
    }

    /**
     * Customer and employee counts for the branch (rarely change — longer TTL).
     */
    public function getCounts(int $branchId): array
    {
        return Cache::remember(
            "dashboard.counts.{$branchId}",
            now()->addMinutes(30),
            fn () => $this->computeCounts($branchId)
        );
    }

    /**
     * Evict all dashboard caches for a branch.
     *
     * Call this from:
     *   - InvoiceController::store() and ::recordPelunasan()
     *   - InvoiceBuildingController::store() and ::recordPelunasan()
     *   - CustomerController::store()
     */
    public function invalidate(int $branchId): void
    {
        $year = now()->year;

        $keys = [
            "dashboard.summary.{$branchId}.{$year}",
            "dashboard.monthly.{$branchId}.{$year}",
            "dashboard.building.{$branchId}.{$year}",
            "dashboard.counts.{$branchId}",
        ];

        // Also bust previous year's cache in January (overlap period)
        if (now()->month === 1) {
            $prev = $year - 1;
            $keys[] = "dashboard.summary.{$branchId}.{$prev}";
            $keys[] = "dashboard.monthly.{$branchId}.{$prev}";
            $keys[] = "dashboard.building.{$branchId}.{$prev}";
        }

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    // ── Teknisi / Kenek dashboard (per-employee, 5-min TTL) ──────────

    /**
     * Active jobs (work_status pending or accepted) for one installer.
     * $foreignKey is 'tukang_id' for teknisi, 'kenek_id' for kenek.
     */
    public function getTeknisiActiveJobs(int $employeeId, string $foreignKey): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            "teknisi.jobs.{$employeeId}.{$foreignKey}",
            now()->addMinutes(self::TTL_TEKNISI),
            fn () => Customer::query()
                ->with(['invoice', 'branch:id,name,city', 'tukang:id,name', 'kenek:id,name'])
                ->where($foreignKey, $employeeId)
                ->whereHas('invoice', fn ($q) => $q->whereIn('work_status', ['pending', 'accepted']))
                ->latest()
                ->get()
        );
    }

    /**
     * Last 10 completed jobs for one installer.
     */
    public function getTeknisiCompletedJobs(int $employeeId, string $foreignKey): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            "teknisi.completed.{$employeeId}.{$foreignKey}",
            now()->addMinutes(self::TTL_TEKNISI),
            fn () => Customer::query()
                ->with(['invoice', 'branch:id,name,city'])
                ->where($foreignKey, $employeeId)
                ->whereHas('invoice', fn ($q) => $q->where('work_status', 'completed'))
                ->latest()
                ->limit(10)
                ->get()
        );
    }

    /**
     * All non-cancelled commissions for one employee (used for list display
     * and to derive the commission summary array in the controller).
     */
    public function getTeknisiCommissions(int $employeeId): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            "teknisi.commissions.{$employeeId}",
            now()->addMinutes(self::TTL_TEKNISI),
            fn () => Commission::query()
                ->with([
                    'customer:id,name,vehicle_brand,vehicle_model,plat_number,install_type',
                    'invoice:id,invoice_number,status',
                    'batch:id,status,batch_number',
                ])
                ->where('employees_id', $employeeId)
                ->whereNotIn('status', ['cancelled'])
                ->latest()
                ->get()
        );
    }

    /**
     * Evict all cached data for a single employee's teknisi dashboard.
     * Call this from TeknisiDashboardController (accept/complete/withdrawal)
     * and from InvoiceController::recordPelunasan() for tukang + kenek.
     */
    public function invalidateTeknisi(int $employeeId): void
    {
        foreach (['tukang_id', 'kenek_id'] as $fk) {
            Cache::forget("teknisi.jobs.{$employeeId}.{$fk}");
            Cache::forget("teknisi.completed.{$employeeId}.{$fk}");
        }
        Cache::forget("teknisi.commissions.{$employeeId}");
    }

    // ── Private computation ───────────────────────────────────────

    private function computeSummary(int $branchId, int $year): array
    {
        // Delegate to RevenueQueryService so the returned shape
        // {car: {year, all}, building: {year, all}, ppf: {year, all}}
        // matches what the dashboard view expects for all user roles.
        return $this->revenue->summary($year, $branchId);
    }

    private function computeMonthlyRevenue(int $branchId, int $year): array
    {
        // Delegate to RevenueQueryService so the returned shape
        // (car_amount, ppf_amount, building_amount, *_count) matches what
        // the dashboard view expects for all user roles.
        return $this->revenue->monthly($year, $branchId);
    }

    private function computeBuildingSummary(int $branchId, int $year): array
    {
        $row = DB::table('invoice_building')
            ->where('branch_id', $branchId)
            ->whereYear('invoice_date', $year)
            ->selectRaw('
                COUNT(*) as total_invoices,
                COALESCE(SUM(total_price), 0)      as total_revenue,
                COALESCE(SUM(down_payment), 0)     as total_dp,
                COALESCE(SUM(remaining_payment), 0) as total_outstanding
            ')
            ->first();

        return $row ? (array) $row : [
            'total_invoices'    => 0,
            'total_revenue'     => 0,
            'total_dp'          => 0,
            'total_outstanding' => 0,
        ];
    }

    private function computeCounts(int $branchId): array
    {
        return [
            'customers'          => DB::table('customers')->where('branch_id', $branchId)->count(),
            'customers_building' => DB::table('customer_building')->where('branch_id', $branchId)->count(),
            'employees_active'   => DB::table('employees')->where('branch_id', $branchId)->where('is_active', true)->count(),
        ];
    }
}
