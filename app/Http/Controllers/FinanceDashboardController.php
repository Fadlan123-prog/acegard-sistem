<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceDashboardController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function dashboard(Request $request)
    {
        $branchId = $this->branch->idOrNull();

        $branchInvoice = fn ($q) => $q->whereHas('customer', fn ($cq) => $cq->where('branch_id', $branchId));

        // KPI stats
        $outstandingInvoices = Invoice::query()
            ->when($branchId, $branchInvoice)
            ->whereIn('status', ['unpaid', 'partial'])
            ->count();

        $outstandingAmount = Invoice::query()
            ->when($branchId, $branchInvoice)
            ->whereIn('status', ['unpaid', 'partial'])
            ->sum('remaining_payment');

        $commissionEligible = DB::table('commissions')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'eligible')
            ->sum('amount');

        $commissionEligibleCount = DB::table('commissions')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'eligible')
            ->count();

        $totalHutang = DB::table('payables')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', '!=', 'paid')
            ->selectRaw('SUM(amount - paid_amount) as total')
            ->value('total') ?? 0;

        $totalPiutang = DB::table('receivables')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', '!=', 'paid')
            ->selectRaw('SUM(amount - paid_amount) as total')
            ->value('total') ?? 0;

        // Pending invoices
        $pendingInvoices = Invoice::with(['customer'])
            ->when($branchId, $branchInvoice)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Commissions to pay per employee
        $commissionsToPay = DB::table('commissions')
            ->join('employees', 'commissions.employees_id', '=', 'employees.id')
            ->when($branchId, fn ($q) => $q->where('commissions.branch_id', $branchId))
            ->where('commissions.status', 'eligible')
            ->select(
                'employees.id',
                'employees.name',
                'employees.job_position',
                DB::raw('SUM(commissions.amount) as total'),
                DB::raw('COUNT(*) as count'),
            )
            ->groupBy('employees.id', 'employees.name', 'employees.job_position')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Hutang jatuh tempo (30 days), with computed remaining_amount
        $hutangDue = DB::table('payables')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', '!=', 'paid')
            ->whereNotNull('due_date')
            ->where('due_date', '<=', now()->addDays(30))
            ->selectRaw('*, (amount - paid_amount) as remaining_amount')
            ->orderBy('due_date')
            ->limit(8)
            ->get();

        $upcomingBirthdays = \App\Models\Employee::upcomingBirthdays($branchId);

        return view('finance.dashboard', compact(
            'outstandingInvoices', 'outstandingAmount',
            'commissionEligible', 'commissionEligibleCount',
            'totalHutang', 'totalPiutang',
            'pendingInvoices', 'commissionsToPay', 'hutangDue',
            'upcomingBirthdays',
        ));
    }
}
