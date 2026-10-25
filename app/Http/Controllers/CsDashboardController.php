<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPpfInstallation;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;

class CsDashboardController extends Controller
{
    public function dashboard()
    {
        // Active leads needing follow-up
        $followUpLeads = Lead::with(['branch'])
            ->whereIn('status', ['new', 'contacted', 'follow_up'])
            ->orderBy('follow_up_date')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        $totalFollowUp = Lead::whereIn('status', ['new', 'contacted', 'follow_up'])->count();

        // Warranty expiring in next 30 days (car/building from customers table)
        $warrantyExpiringCar = Customer::with(['branch'])
            ->where('warantee_end', '>=', today())
            ->where('warantee_end', '<=', today()->addDays(30))
            ->orderBy('warantee_end')
            ->limit(10)
            ->get();

        // Warranty expiring PPF (next 30 days)
        $warrantyExpiringPpf = CustomerPpfInstallation::with(['customerPpf', 'branch'])
            ->where('warranty_status', 'active')
            ->where('warranty_end', '>=', today())
            ->where('warranty_end', '<=', today()->addDays(30))
            ->orderBy('warranty_end')
            ->limit(10)
            ->get();

        // Invoices with outstanding balance (pelunasan reminder)
        $pendingPayments = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->whereIn('invoices.status', ['unpaid', 'partial'])
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
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Stats
        $totalPendingPayments    = DB::table('invoices')->whereIn('status', ['unpaid', 'partial'])->count();
        $totalOutstandingAmount  = (float) DB::table('invoices')->whereIn('status', ['unpaid', 'partial'])->sum('remaining_payment');
        $totalActiveClaims       = DB::table('warranty_claims')->whereIn('status', ['submitted', 'under_review'])->count();

        $upcomingBirthdays = \App\Models\Employee::upcomingBirthdays();

        return view('cs.dashboard', compact(
            'followUpLeads', 'totalFollowUp',
            'warrantyExpiringCar', 'warrantyExpiringPpf',
            'pendingPayments', 'recentClaims',
            'totalPendingPayments', 'totalOutstandingAmount', 'totalActiveClaims',
            'upcomingBirthdays',
        ));
    }
}
