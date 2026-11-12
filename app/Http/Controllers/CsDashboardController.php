<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPpfInstallation;
use App\Models\Lead;
use App\Services\BranchContext;
use Illuminate\Support\Facades\DB;

class CsDashboardController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function dashboard()
    {
        $branchId = $this->branch->idOrNull();
        $isGlobal = auth()->user()->hasRole('superadmin');

        // Active leads needing follow-up
        $followUpLeads = Lead::with(['branch'])
            ->whereIn('status', ['new', 'contacted', 'follow_up'])
            ->when(! $isGlobal, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('follow_up_date')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        $totalFollowUp = Lead::whereIn('status', ['new', 'contacted', 'follow_up'])
            ->when(! $isGlobal, fn ($q) => $q->where('branch_id', $branchId))
            ->count();

        // Warranty expiring in next 30 days (car/building from customers table)
        $warrantyExpiringCar = Customer::with(['branch'])
            ->where('warantee_end', '>=', today())
            ->where('warantee_end', '<=', today()->addDays(30))
            ->when(! $isGlobal, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('warantee_end')
            ->limit(10)
            ->get();

        // Warranty expiring PPF (next 30 days)
        $warrantyExpiringPpf = CustomerPpfInstallation::with(['customerPpf', 'branch'])
            ->where('warranty_status', 'active')
            ->where('warranty_end', '>=', today())
            ->where('warranty_end', '<=', today()->addDays(30))
            ->when(! $isGlobal, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('warranty_end')
            ->limit(10)
            ->get();

        // Invoices with outstanding balance (pelunasan reminder)
        $pendingPayments = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->whereIn('invoices.status', ['unpaid', 'partial'])
            ->when(! $isGlobal, fn ($q) => $q->where('customers.branch_id', $branchId))
            ->select(
                'invoices.id',
                'invoices.invoice_number',
                'invoices.total_price',
                'invoices.remaining_payment',
                'invoices.created_at',
                'customers.name as customer_name',
                'customers.phone_number as customer_phone',
            )
            ->orderByDesc('invoices.remaining_payment')
            ->limit(15)
            ->get();

        // Recent warranty claims
        $recentClaims = DB::table('warranty_claims')
            ->whereIn('status', ['submitted', 'under_review'])
            ->when(! $isGlobal, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Stats — `invoices` has no branch_id of its own, join via customers.
        $totalPendingPayments = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->whereIn('invoices.status', ['unpaid', 'partial'])
            ->when(! $isGlobal, fn ($q) => $q->where('customers.branch_id', $branchId))
            ->count();
        $totalOutstandingAmount = (float) DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->whereIn('invoices.status', ['unpaid', 'partial'])
            ->when(! $isGlobal, fn ($q) => $q->where('customers.branch_id', $branchId))
            ->sum('invoices.remaining_payment');
        $totalActiveClaims = DB::table('warranty_claims')->whereIn('status', ['submitted', 'under_review'])
            ->when(! $isGlobal, fn ($q) => $q->where('branch_id', $branchId))
            ->count();

        $upcomingBirthdays = \App\Models\Employee::upcomingBirthdays();

        // Closing rate per CS agent this month, same pattern as
        // EmployeeRankingController::marketing() — leads assigned to each employee.
        $closingByAgent = DB::table('leads')
            ->join('employees', 'leads.assigned_to', '=', 'employees.id')
            ->whereNotNull('leads.assigned_to')
            ->whereMonth('leads.created_at', now()->month)
            ->whereYear('leads.created_at', now()->year)
            ->when(! $isGlobal, fn ($q) => $q->where('leads.branch_id', $branchId))
            ->select(
                'employees.id',
                'employees.name',
                DB::raw('COUNT(*) as total_leads'),
                DB::raw('SUM(CASE WHEN leads.status = "won" THEN 1 ELSE 0 END) as won_leads'),
            )
            ->groupBy('employees.id', 'employees.name')
            ->orderByDesc('total_leads')
            ->get()
            ->map(function ($row) {
                $row->closing_rate = $row->total_leads > 0
                    ? round($row->won_leads / $row->total_leads * 100, 1)
                    : 0;
                return $row;
            });

        $teamClosingRate = $closingByAgent->sum('total_leads') > 0
            ? round($closingByAgent->sum('won_leads') / $closingByAgent->sum('total_leads') * 100, 1)
            : 0;

        return view('cs.dashboard', compact(
            'followUpLeads', 'totalFollowUp',
            'warrantyExpiringCar', 'warrantyExpiringPpf',
            'pendingPayments', 'recentClaims',
            'totalPendingPayments', 'totalOutstandingAmount', 'totalActiveClaims',
            'upcomingBirthdays',
            'closingByAgent', 'teamClosingRate',
        ));
    }
}
