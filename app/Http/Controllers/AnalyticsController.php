<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\BranchContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function index(Request $request)
    {
        $user         = auth()->user();
        $isSuperadmin = $user->hasRole('superadmin');
        $branches     = $isSuperadmin ? Branch::orderBy('name')->get() : collect();

        [$startDate, $endDate] = $this->parseDateRange($request);

        $branchId = $this->activeBranchId($user, $request);

        // ── Revenue ───────────────────────────────────────────────────────────

        $revenue = $this->getRevenueSummary($startDate, $endDate, $branchId, $isSuperadmin);
        $revenueByDay = $this->getRevenueByDay($startDate, $endDate, $branchId, $isSuperadmin);
        $revenueByType = $this->getRevenueByType($startDate, $endDate, $branchId, $isSuperadmin);

        // ── Commissions ───────────────────────────────────────────────────────

        $commissionStats = $this->getCommissionStats($startDate, $endDate, $branchId, $isSuperadmin);
        $topMarketing    = $this->getTopByRole('marketing', $startDate, $endDate, $branchId, $isSuperadmin);
        $topTeknisi      = $this->getTopByRole('teknisi', $startDate, $endDate, $branchId, $isSuperadmin);

        // ── Hutang & Piutang Aging ────────────────────────────────────────────

        $hutangAging   = $this->getAging('payables', 'vendor_name', $branchId, $isSuperadmin);
        $piutangAging  = $this->getAging('receivables', 'customer_name', $branchId, $isSuperadmin);

        // ── Petty Cash ────────────────────────────────────────────────────────

        $pettyCash = $this->getPettyCashSummary($startDate, $endDate, $branchId, $isSuperadmin);

        // ── Branch comparison (superadmin) ────────────────────────────────────

        $branchRevenue = $isSuperadmin
            ? $this->getRevenueByBranch($startDate, $endDate)
            : collect();

        return view('analytics.index', compact(
            'isSuperadmin', 'branches', 'branchId',
            'startDate', 'endDate',
            'revenue', 'revenueByDay', 'revenueByType',
            'commissionStats', 'topMarketing', 'topTeknisi',
            'hutangAging', 'piutangAging',
            'pettyCash', 'branchRevenue'
        ) + ['filters' => $request->only(['branch_id', 'start', 'end', 'period'])]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function parseDateRange(Request $request): array
    {
        $period = $request->get('period', 'month');

        if ($request->filled('start') && $request->filled('end')) {
            return [
                Carbon::parse($request->start)->startOfDay(),
                Carbon::parse($request->end)->endOfDay(),
            ];
        }

        return match ($period) {
            '3months' => [now()->subMonths(2)->startOfMonth(), now()->endOfMonth()],
            'year'    => [now()->startOfYear(), now()->endOfYear()],
            default   => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    private function activeBranchId($user, Request $request): ?int
    {
        if ($user->hasRole('superadmin')) {
            return $request->filled('branch_id') ? (int) $request->branch_id : null;
        }

        $id = $this->branch->idOrNull()
            ?? (int) $user->branches()->orderBy('branches.id')->value('branches.id');

        return $id ?: null;
    }

    private function getRevenueSummary(Carbon $start, Carbon $end, ?int $branchId, bool $isSuperadmin): array
    {
        // Window Tinting (via customers.branch_id)
        $wtQuery = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->where('invoices.status', 'paid')
            ->whereBetween('invoices.invoice_date', [$start, $end])
            ->when(!$isSuperadmin && $branchId, fn ($q) => $q->where('customers.branch_id', $branchId))
            ->when($isSuperadmin && $branchId,  fn ($q) => $q->where('customers.branch_id', $branchId));

        $wt = [
            'total' => (int) (clone $wtQuery)->sum('invoices.total_price'),
            'count' => (clone $wtQuery)->count(),
        ];

        // PPF (has branch_id)
        $ppfQuery = DB::table('ppf_invoices')
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$start, $end])
            ->when(!$isSuperadmin && $branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($isSuperadmin && $branchId,  fn ($q) => $q->where('branch_id', $branchId));

        $ppf = [
            'total' => (int) (clone $ppfQuery)->sum('total_price'),
            'count' => (clone $ppfQuery)->count(),
        ];

        // Building
        $bldQuery = DB::table('invoice_building')
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$start, $end])
            ->when(!$isSuperadmin && $branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($isSuperadmin && $branchId,  fn ($q) => $q->where('branch_id', $branchId));

        $bld = [
            'total' => (int) (clone $bldQuery)->sum('total_price'),
            'count' => (clone $bldQuery)->count(),
        ];

        $grandTotal = $wt['total'] + $ppf['total'] + $bld['total'];
        $grandCount = $wt['count'] + $ppf['count'] + $bld['count'];

        return [
            'grand_total'  => $grandTotal,
            'grand_count'  => $grandCount,
            'avg'          => $grandCount > 0 ? (int) round($grandTotal / $grandCount) : 0,
            'wt_total'     => $wt['total'],
            'wt_count'     => $wt['count'],
            'ppf_total'    => $ppf['total'],
            'ppf_count'    => $ppf['count'],
            'bld_total'    => $bld['total'],
            'bld_count'    => $bld['count'],
        ];
    }

    private function getRevenueByDay(Carbon $start, Carbon $end, ?int $branchId, bool $isSuperadmin): array
    {
        $days = [];
        $cur  = $start->copy();
        while ($cur->lte($end)) {
            $days[$cur->format('Y-m-d')] = ['wt' => 0, 'ppf' => 0, 'bld' => 0];
            $cur->addDay();
        }

        // Window Tinting
        $wtRows = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->selectRaw('DATE(invoices.invoice_date) as d, SUM(invoices.total_price) as total')
            ->where('invoices.status', 'paid')
            ->whereBetween('invoices.invoice_date', [$start, $end])
            ->when(!$isSuperadmin && $branchId, fn ($q) => $q->where('customers.branch_id', $branchId))
            ->when($isSuperadmin && $branchId,  fn ($q) => $q->where('customers.branch_id', $branchId))
            ->groupBy('d')->get();
        foreach ($wtRows as $r) {
            if (isset($days[$r->d])) $days[$r->d]['wt'] = (int) $r->total;
        }

        // PPF
        $ppfRows = DB::table('ppf_invoices')
            ->selectRaw('DATE(invoice_date) as d, SUM(total_price) as total')
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$start, $end])
            ->when(!$isSuperadmin && $branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($isSuperadmin && $branchId,  fn ($q) => $q->where('branch_id', $branchId))
            ->groupBy('d')->get();
        foreach ($ppfRows as $r) {
            if (isset($days[$r->d])) $days[$r->d]['ppf'] = (int) $r->total;
        }

        // Building
        $bldRows = DB::table('invoice_building')
            ->selectRaw('DATE(invoice_date) as d, SUM(total_price) as total')
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$start, $end])
            ->when(!$isSuperadmin && $branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($isSuperadmin && $branchId,  fn ($q) => $q->where('branch_id', $branchId))
            ->groupBy('d')->get();
        foreach ($bldRows as $r) {
            if (isset($days[$r->d])) $days[$r->d]['bld'] = (int) $r->total;
        }

        return $days;
    }

    private function getRevenueByType(Carbon $start, Carbon $end, ?int $branchId, bool $isSuperadmin): array
    {
        $rev = $this->getRevenueSummary($start, $end, $branchId, $isSuperadmin);

        return [
            'labels' => ['Window Tinting', 'PPF', 'Gedung'],
            'values' => [$rev['wt_total'], $rev['ppf_total'], $rev['bld_total']],
        ];
    }

    private function getRevenueByBranch(Carbon $start, Carbon $end): array
    {
        $branches = Branch::orderBy('name')->get();
        $data     = [];

        foreach ($branches as $br) {
            $wtTotal  = (int) DB::table('invoices')
                ->join('customers', 'invoices.customer_id', '=', 'customers.id')
                ->where('invoices.status', 'paid')
                ->whereBetween('invoices.invoice_date', [$start, $end])
                ->where('customers.branch_id', $br->id)
                ->sum('invoices.total_price');

            $ppfTotal = (int) DB::table('ppf_invoices')
                ->where('status', 'paid')
                ->whereBetween('invoice_date', [$start, $end])
                ->where('branch_id', $br->id)
                ->sum('total_price');

            $bldTotal = (int) DB::table('invoice_building')
                ->where('status', 'paid')
                ->whereBetween('invoice_date', [$start, $end])
                ->where('branch_id', $br->id)
                ->sum('total_price');

            $data[] = [
                'name'  => $br->name,
                'total' => $wtTotal + $ppfTotal + $bldTotal,
            ];
        }

        return $data;
    }

    private function getCommissionStats(Carbon $start, Carbon $end, ?int $branchId, bool $isSuperadmin): array
    {
        $q = DB::table('commissions')
            ->whereBetween('created_at', [$start, $end])
            ->when(!$isSuperadmin && $branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($isSuperadmin && $branchId,  fn ($q) => $q->where('branch_id', $branchId));

        return [
            'total_eligible' => (int) (clone $q)->where('status', 'eligible')->sum('amount'),
            'total_paid'     => (int) (clone $q)->where('status', 'paid')->sum('amount'),
            'total_pending'  => (int) (clone $q)->where('status', 'pending')->sum('amount'),
        ];
    }

    private function getTopByRole(string $role, Carbon $start, Carbon $end, ?int $branchId, bool $isSuperadmin): array
    {
        return DB::table('commissions')
            ->join('employees', 'commissions.employees_id', '=', 'employees.id')
            ->selectRaw('employees.id, employees.name, SUM(commissions.amount) as total, COUNT(*) as cnt')
            ->where('commissions.role', $role)
            ->whereIn('commissions.status', ['eligible', 'paid'])
            ->whereBetween('commissions.created_at', [$start, $end])
            ->when(!$isSuperadmin && $branchId, fn ($q) => $q->where('commissions.branch_id', $branchId))
            ->when($isSuperadmin && $branchId,  fn ($q) => $q->where('commissions.branch_id', $branchId))
            ->groupBy('employees.id', 'employees.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->toArray();
    }

    private function getAging(string $table, string $nameCol, ?int $branchId, bool $isSuperadmin): array
    {
        $today = now()->toDateString();

        $q = DB::table($table)
            ->whereNotIn('status', ['paid'])
            ->whereNotNull('due_date')
            ->when(!$isSuperadmin && $branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($isSuperadmin && $branchId,  fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw("
                {$nameCol} as name,
                amount, paid_amount, due_date,
                DATEDIFF('{$today}', due_date) as days_overdue
            ")
            ->orderByDesc('days_overdue');

        $rows = $q->get();

        $buckets = ['current' => 0, '1_30' => 0, '31_60' => 0, '61_90' => 0, 'over90' => 0];

        foreach ($rows as $r) {
            $remaining = $r->amount - $r->paid_amount;
            $days      = (int) $r->days_overdue;

            if ($days <= 0)       $buckets['current'] += $remaining;
            elseif ($days <= 30)  $buckets['1_30']    += $remaining;
            elseif ($days <= 60)  $buckets['31_60']   += $remaining;
            elseif ($days <= 90)  $buckets['61_90']   += $remaining;
            else                  $buckets['over90']  += $remaining;
        }

        return ['buckets' => $buckets, 'items' => $rows->toArray()];
    }

    private function getPettyCashSummary(Carbon $start, Carbon $end, ?int $branchId, bool $isSuperadmin): array
    {
        $q = DB::table('petty_cash_transactions')
            ->whereBetween('date', [$start, $end])
            ->when(!$isSuperadmin && $branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($isSuperadmin && $branchId,  fn ($q) => $q->where('branch_id', $branchId));

        return [
            'in'       => (int) (clone $q)->where('type', 'in')->sum('amount'),
            'out'      => (int) (clone $q)->where('type', 'out')->sum('amount'),
            'purchase' => (int) (clone $q)->where('type', 'purchase')->sum('amount'),
        ];
    }
}
