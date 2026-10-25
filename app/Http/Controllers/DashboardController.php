<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\FilmStockPool;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\User;
use App\Services\BranchContext;
use App\Services\CommissionAlertService;
use App\Services\DashboardCacheService;
use App\Services\RevenueQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardCacheService $cache,
        private RevenueQueryService   $revenue,
        private BranchContext         $branch,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('superadmin')) {
            return $this->superadminDashboard($request);
        }

        if ($user->hasRole('admin')) {
            return $this->adminDashboard($request);
        }

        if ($user->hasRole('manager_operasional')) {
            return redirect()->route('manager-ops.dashboard');
        }

        if ($user->hasRole('finance')) {
            return redirect()->route('finance.dashboard');
        }

        if ($user->hasRole('purchasing')) {
            return redirect()->route('purchasing.dashboard');
        }

        if ($user->hasRole('manager_marketing')) {
            return redirect()->route('marketing.dashboard');
        }

        if ($user->hasAnyRole(['teknisi', 'kenek'])) {
            return redirect()->route('teknisi.dashboard');
        }

        abort(403, 'Akses ditolak.');
    }

    // ── Superadmin dashboard ──────────────────────────────────────────────────

    private function superadminDashboard(Request $request)
    {
        $year = (int) $request->get('year', now()->year);

        try {
            $alertBranchId = $this->branch->idOrNull();
            if ($alertBranchId) {
                app(CommissionAlertService::class)->checkAndNotify($alertBranchId);
            }
        } catch (\Throwable) {
            // Alert failure must never block the dashboard
        }

        if ($request->filled('branch_id')) {
            $branchId = (int) $request->get('branch_id');
            $this->branch->switchTo($branchId);
        } else {
            $branchId = $this->branch->idOrNull();
        }

        $branch = $branchId ? Branch::find($branchId) : null;

        $summary = $branchId
            ? $this->cache->getSummary($branchId, $year)
            : $this->revenue->summary($year, null);

        return view('dashboard.index', [
            'title'          => 'Dashboard',
            'isSuperadmin'   => true,
            'totalUsers'     => User::count(),
            'totalCustomers' => Customer::count(),
            'totalBranches'  => Branch::count(),
            'branches'       => Branch::orderBy('name')->get(['id', 'name', 'city']),

            'selectedYear'   => $year,
            'years'          => $this->revenue->availableYears(),
            'selectedBranch' => $branch,

            'summary'        => $summary,
            'monthly'        => $branchId
                                    ? $this->cache->getMonthlyRevenue($branchId, $year)
                                    : $this->revenue->monthly($year, null),
            'yearly'         => $this->revenue->yearly($branchId),

            'rows'           => $this->getBuildingCategorySummary(),
            'products'       => $this->getBuildingProductSummary(),

            'topMarketing'   => $this->getTopMarketing($year, $branchId),
            'topTeknisi'     => $this->getTopTeknisi($year, $branchId),
            'stockAlerts'    => $this->getStockAlerts($branchId),
            'plSummary'      => $this->getPlSummary($year, $branchId, $summary),
            'upcomingBirthdays' => \App\Models\Employee::upcomingBirthdays($branchId),
        ]);
    }

    // ── Admin dashboard ───────────────────────────────────────────────────────

    private function adminDashboard(Request $request)
    {
        $activeBranchId = $this->branch->idOrNull();

        if (!$activeBranchId) {
            $activeBranchId = (int) $request->user()
                ->branches()
                ->orderBy('branches.id')
                ->value('branches.id');
        }

        if (!$activeBranchId) {
            abort(403, 'Akun admin belum memiliki akses cabang.');
        }

        $branch = Branch::find($activeBranchId);

        if (!$branch) {
            $this->branch->flush();
            abort(403, 'Cabang aktif tidak valid.');
        }

        $year = (int) $request->get('year', now()->year);

        $summary = $this->cache->getSummary($activeBranchId, $year);

        return view('dashboard.index', [
            'title'          => 'Dashboard — ' . $branch->name,
            'isSuperadmin'   => false,
            'branch'         => $branch,
            'totalUsers'     => null,
            'totalCustomers' => $this->cache->getCounts($activeBranchId)['customers'],
            'totalBranches'  => null,
            'branches'       => collect(),

            'selectedYear'   => $year,
            'years'          => $this->revenue->availableYears(),
            'selectedBranch' => $branch,

            'summary'        => $summary,
            'monthly'        => $this->cache->getMonthlyRevenue($activeBranchId, $year),
            'yearly'         => $this->revenue->yearly($activeBranchId),

            'rows'           => collect(),
            'products'       => collect(),

            'topMarketing'   => $this->getTopMarketing($year, $activeBranchId),
            'topTeknisi'     => $this->getTopTeknisi($year, $activeBranchId),
            'stockAlerts'    => $this->getStockAlerts($activeBranchId),
            'plSummary'      => $this->getPlSummary($year, $activeBranchId, $summary),
            'upcomingBirthdays' => \App\Models\Employee::upcomingBirthdays($activeBranchId),
        ]);
    }

    // ── CRM Dashboard ─────────────────────────────────────────────────────────

    public function crmDashboard()
    {
        $leadFunnel   = Lead::accessibleByUser()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $leadSources  = Lead::accessibleByUser()
            ->selectRaw('source, COUNT(*) as count')
            ->groupBy('source')
            ->pluck('count', 'source');

        $totalLeads      = Lead::accessibleByUser()->count();
        $wonLeads        = Lead::accessibleByUser()->where('status', 'won')->count();
        $leadsThisMonth  = Lead::accessibleByUser()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $quotationStats  = Quotation::accessibleByUser()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $soPipeline      = SalesOrder::accessibleByUser()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $activeSo        = SalesOrder::accessibleByUser()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $spkPending      = SalesOrder::accessibleByUser()
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->whereNull('spk_number')
            ->count();

        $upcomingInstallations = SalesOrder::accessibleByUser()
            ->with(['tukang', 'technicians', 'branch'])
            ->whereNotNull('scheduled_date')
            ->whereBetween('scheduled_date', [today(), today()->addDays(7)])
            ->orderBy('scheduled_date')
            ->get();

        $recentLeads     = Lead::accessibleByUser()
            ->with(['branch'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('dashboard.crm', compact(
            'leadFunnel', 'leadSources',
            'totalLeads', 'wonLeads', 'leadsThisMonth',
            'quotationStats', 'soPipeline',
            'activeSo', 'spkPending',
            'upcomingInstallations', 'recentLeads',
        ));
    }

    // ── Owner dashboard extras ────────────────────────────────────────────────

    private function getTopMarketing(int $year, ?int $branchId): \Illuminate\Support\Collection
    {
        return DB::table('commissions')
            ->join('employees', 'commissions.employees_id', '=', 'employees.id')
            ->where('commissions.role', 'marketing')
            ->whereIn('commissions.status', ['eligible', 'paid'])
            ->whereYear('commissions.created_at', $year)
            ->when($branchId, fn ($q) => $q->where('commissions.branch_id', $branchId))
            ->select(
                'employees.id',
                'employees.name',
                'employees.job_position',
                DB::raw('SUM(commissions.amount) as total_commission'),
                DB::raw('COUNT(*) as job_count'),
            )
            ->groupBy('employees.id', 'employees.name', 'employees.job_position')
            ->orderByDesc('total_commission')
            ->limit(5)
            ->get();
    }

    private function getTopTeknisi(int $year, ?int $branchId): \Illuminate\Support\Collection
    {
        return DB::table('commissions')
            ->join('employees', 'commissions.employees_id', '=', 'employees.id')
            ->whereIn('commissions.role', ['teknisi', 'kenek', 'detailer'])
            ->whereIn('commissions.status', ['eligible', 'paid'])
            ->whereYear('commissions.created_at', $year)
            ->when($branchId, fn ($q) => $q->where('commissions.branch_id', $branchId))
            ->select(
                'employees.id',
                'employees.name',
                'employees.job_position',
                DB::raw('SUM(commissions.amount) as total_commission'),
                DB::raw('COUNT(*) as job_count'),
            )
            ->groupBy('employees.id', 'employees.name', 'employees.job_position')
            ->orderByDesc('total_commission')
            ->limit(5)
            ->get();
    }

    private function getStockAlerts(?int $branchId): \Illuminate\Support\Collection
    {
        return FilmStockPool::with(['branch'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('remaining_length_meter', '<=', 10)
            ->orderBy('remaining_length_meter')
            ->limit(8)
            ->get();
    }

    private function getPlSummary(int $year, ?int $branchId, array $summary): array
    {
        $revenue = ($summary['car']['year']['paid']      ?? 0)
                 + ($summary['building']['year']['paid']  ?? 0)
                 + ($summary['ppf']['year']['paid']       ?? 0);

        $commissionsPaid = DB::table('commissions')
            ->where('status', 'paid')
            ->whereYear('paid_at', $year)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('amount');

        $expenses = DB::table('petty_cash_transactions')
            ->whereIn('type', ['out', 'purchase'])
            ->whereYear('created_at', $year)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('amount');

        return [
            'revenue'          => (float) $revenue,
            'commissions_paid' => (float) $commissionsPaid,
            'expenses'         => (float) $expenses,
            'estimated_profit' => (float) ($revenue - $commissionsPaid - $expenses),
        ];
    }

    // ── Building summaries (superadmin only, not cached — small tables) ───────

    private function getBuildingCategorySummary()
    {
        return DB::table('customer_building_product as cbp')
            ->join('category_product_building as cat', 'cat.id', '=', 'cbp.category_product_building_id')
            ->groupBy('cat.id', 'cat.name')
            ->orderBy('cat.name')
            ->select([
                'cat.id',
                'cat.name',
                DB::raw('COALESCE(SUM(cbp.meters),0) AS total_meters'),
                DB::raw('COUNT(*) AS total_rows'),
            ])
            ->get();
    }

    private function getBuildingProductSummary()
    {
        return DB::table('customer_building_product as cbp')
            ->join('product_building as p', 'p.id', '=', 'cbp.product_building_id')
            ->join('category_product_building as c', 'c.id', '=', 'p.category_product_building_id')
            ->groupBy('p.id', 'p.name', 'c.name')
            ->orderBy('c.name')
            ->orderBy('p.name')
            ->select([
                'p.id',
                'p.name',
                'c.name as category_name',
                DB::raw('COALESCE(SUM(cbp.meters),0) AS total_meters'),
                DB::raw('COUNT(*) AS total_rows'),
            ])
            ->get();
    }
}
