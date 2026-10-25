<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class OwnerDashboardController extends Controller
{
    public function dashboard()
    {
        $today = today();
        $month = now()->month;
        $year  = now()->year;

        // Revenue today (paid invoices, joined to customers for branch)
        $revenueToday = DB::table('invoices')
            ->whereDate('invoices.created_at', $today)
            ->where('invoices.status', 'paid')
            ->sum('invoices.total_price');

        $revenueToday += DB::table('ppf_invoices')
            ->whereDate('ppf_invoices.created_at', $today)
            ->where('ppf_invoices.status', 'paid')
            ->sum('ppf_invoices.total_price');

        // Revenue this month (paid)
        $revenueMonth = DB::table('invoices')
            ->whereMonth('invoices.created_at', $month)
            ->whereYear('invoices.created_at', $year)
            ->where('invoices.status', 'paid')
            ->sum('invoices.total_price');

        $revenueMonth += DB::table('ppf_invoices')
            ->whereMonth('ppf_invoices.created_at', $month)
            ->whereYear('ppf_invoices.created_at', $year)
            ->where('ppf_invoices.status', 'paid')
            ->sum('ppf_invoices.total_price');

        // Outstanding invoices count
        $outstandingCount = DB::table('invoices')
            ->whereIn('status', ['unpaid', 'partial'])
            ->count();

        $outstandingCount += DB::table('ppf_invoices')
            ->whereIn('status', ['unpaid', 'partial'])
            ->count();

        // Outstanding amount (remaining_payment)
        $outstandingAmount = (float) DB::table('invoices')
            ->whereIn('status', ['unpaid', 'partial'])
            ->sum('remaining_payment');

        $outstandingAmount += (float) DB::table('ppf_invoices')
            ->whereIn('status', ['unpaid', 'partial'])
            ->sum('remaining_payment');

        // Top marketing this month
        $topMarketing = DB::table('commissions')
            ->join('employees', 'commissions.employees_id', '=', 'employees.id')
            ->where('commissions.role', 'marketing')
            ->whereIn('commissions.status', ['eligible', 'paid'])
            ->whereMonth('commissions.created_at', $month)
            ->whereYear('commissions.created_at', $year)
            ->select(
                'employees.name',
                DB::raw('SUM(commissions.amount) as total_commission'),
                DB::raw('COUNT(*) as job_count'),
            )
            ->groupBy('employees.id', 'employees.name')
            ->orderByDesc('total_commission')
            ->limit(5)
            ->get();

        // Top teknisi this month
        $topTeknisi = DB::table('commissions')
            ->join('employees', 'commissions.employees_id', '=', 'employees.id')
            ->whereIn('commissions.role', ['teknisi', 'kenek', 'detailer'])
            ->whereIn('commissions.status', ['eligible', 'paid'])
            ->whereMonth('commissions.created_at', $month)
            ->whereYear('commissions.created_at', $year)
            ->select(
                'employees.name',
                DB::raw('SUM(commissions.amount) as total_commission'),
                DB::raw('COUNT(*) as job_count'),
            )
            ->groupBy('employees.id', 'employees.name')
            ->orderByDesc('total_commission')
            ->limit(5)
            ->get();

        // Revenue by branch this month (via customers join)
        $revenueByBranch = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->join('branches', 'customers.branch_id', '=', 'branches.id')
            ->whereMonth('invoices.created_at', $month)
            ->whereYear('invoices.created_at', $year)
            ->where('invoices.status', 'paid')
            ->groupBy('branches.id', 'branches.name')
            ->select('branches.name', DB::raw('SUM(invoices.total_price) as total'))
            ->orderByDesc('total')
            ->get();

        $totalCustomers = Customer::count();
        $totalBranches  = Branch::count();

        // Recent invoices
        $recentInvoices = Invoice::with(['customer'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $upcomingBirthdays = \App\Models\Employee::upcomingBirthdays();

        return view('owner.dashboard', compact(
            'revenueToday', 'revenueMonth',
            'outstandingCount', 'outstandingAmount',
            'topMarketing', 'topTeknisi',
            'revenueByBranch',
            'totalCustomers', 'totalBranches',
            'recentInvoices',
            'upcomingBirthdays',
        ));
    }
}
