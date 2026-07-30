<?php

namespace App\Http\Controllers;

use App\Services\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeRankingController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function teknisi(Request $request)
    {
        $branchId = $this->branch->idOrNull();
        $month    = (int) $request->query('month', now()->month);
        $year     = (int) $request->query('year', now()->year);

        // Teknisi ranking by jobs completed and commissions earned
        $ranking = DB::table('commissions')
            ->join('employees', 'commissions.employees_id', '=', 'employees.id')
            ->whereIn('commissions.role', ['tukang', 'teknisi'])
            ->whereMonth('commissions.created_at', $month)
            ->whereYear('commissions.created_at', $year)
            ->whereNotIn('commissions.status', ['cancelled'])
            ->when($branchId, fn ($q) => $q->where('commissions.branch_id', $branchId))
            ->select(
                'employees.id',
                'employees.name',
                'employees.job_position',
                DB::raw('COUNT(*) as total_jobs'),
                DB::raw('SUM(CASE WHEN commissions.status = "paid" THEN commissions.amount ELSE 0 END) as paid_total'),
                DB::raw('SUM(CASE WHEN commissions.status IN ("eligible","paid") THEN commissions.amount ELSE 0 END) as earned_total'),
            )
            ->groupBy('employees.id', 'employees.name', 'employees.job_position')
            ->orderByDesc('earned_total')
            ->limit(20)
            ->get();

        // Kenek ranking
        $rankingKenek = DB::table('commissions')
            ->join('employees', 'commissions.employees_id', '=', 'employees.id')
            ->where('commissions.role', 'kenek')
            ->whereMonth('commissions.created_at', $month)
            ->whereYear('commissions.created_at', $year)
            ->whereNotIn('commissions.status', ['cancelled'])
            ->when($branchId, fn ($q) => $q->where('commissions.branch_id', $branchId))
            ->select(
                'employees.id',
                'employees.name',
                'employees.job_position',
                DB::raw('COUNT(*) as total_jobs'),
                DB::raw('SUM(CASE WHEN commissions.status IN ("eligible","paid") THEN commissions.amount ELSE 0 END) as earned_total'),
            )
            ->groupBy('employees.id', 'employees.name', 'employees.job_position')
            ->orderByDesc('earned_total')
            ->limit(20)
            ->get();

        return view('ranking.teknisi', [
            'title'         => 'Ranking Teknisi',
            'ranking'       => $ranking,
            'rankingKenek'  => $rankingKenek,
            'month'         => $month,
            'year'          => $year,
        ]);
    }

    public function marketing(Request $request)
    {
        $branchId = $this->branch->idOrNull();
        $month    = (int) $request->query('month', now()->month);
        $year     = (int) $request->query('year', now()->year);

        // Marketing ranking by omset (invoice total) and commission earned
        $ranking = DB::table('commissions')
            ->join('employees', 'commissions.employees_id', '=', 'employees.id')
            ->leftJoin('invoices', 'commissions.invoice_id', '=', 'invoices.id')
            ->where('commissions.role', 'marketing')
            ->whereMonth('commissions.created_at', $month)
            ->whereYear('commissions.created_at', $year)
            ->whereNotIn('commissions.status', ['cancelled'])
            ->when($branchId, fn ($q) => $q->where('commissions.branch_id', $branchId))
            ->select(
                'employees.id',
                'employees.name',
                'employees.job_position',
                DB::raw('COUNT(*) as total_deals'),
                DB::raw('SUM(COALESCE(invoices.total_price, 0)) as total_omset'),
                DB::raw('SUM(CASE WHEN commissions.status IN ("eligible","paid") THEN commissions.amount ELSE 0 END) as earned_commission'),
            )
            ->groupBy('employees.id', 'employees.name', 'employees.job_position')
            ->orderByDesc('total_omset')
            ->limit(20)
            ->get();

        // Also calculate closing rate: won leads / total leads (this month or all time)
        $leadStats = DB::table('leads')
            ->select(
                'created_by',
                DB::raw('COUNT(*) as total_leads'),
                DB::raw('SUM(CASE WHEN status = "won" THEN 1 ELSE 0 END) as won_leads'),
            )
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->groupBy('created_by')
            ->get()
            ->keyBy('created_by');

        $userToEmployee = DB::table('users')
            ->whereNotNull('employee_id')
            ->pluck('employee_id', 'id');

        return view('ranking.marketing', [
            'title'       => 'Ranking Marketing',
            'ranking'     => $ranking,
            'leadStats'   => $leadStats,
            'userToEmployee' => $userToEmployee,
            'month'       => $month,
            'year'        => $year,
        ]);
    }
}
