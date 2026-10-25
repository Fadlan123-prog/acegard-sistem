<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Models\FilmStockPool;
use App\Services\BranchContext;
use Illuminate\Http\Request;

class ManagerOpsDashboardController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function dashboard(Request $request)
    {
        $branchId = $this->branch->idOrNull();

        $branchInvoice = fn ($q) => $q->whereHas('customer', fn ($cq) => $cq->where('branch_id', $branchId));

        // KPI stats
        $installationsToday = Customer::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('install_date', today())
            ->count();

        $pendingRelease = Invoice::query()
            ->when($branchId, $branchInvoice)
            ->whereNull('commission_released_at')
            ->where('status', 'paid')
            ->count();

        $activeJobs = SalesOrder::accessibleByUser()
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->count();

        $lowStock = FilmStockPool::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('remaining_length_meter', '<=', 10)
            ->count();

        // Pending commission release list
        $pendingInvoices = Invoice::with(['customer'])
            ->when($branchId, $branchInvoice)
            ->whereNull('commission_released_at')
            ->where('status', 'paid')
            ->latest()
            ->limit(10)
            ->get();

        // Upcoming installations (next 7 days)
        $upcomingInstallations = SalesOrder::accessibleByUser()
            ->with(['tukang', 'kenek', 'technicians'])
            ->whereNotNull('scheduled_date')
            ->whereBetween('scheduled_date', [today(), today()->addDays(7)])
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderBy('scheduled_date')
            ->get();

        // Low stock alerts
        $stockAlerts = FilmStockPool::with(['branch'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('remaining_length_meter', '<=', 10)
            ->orderBy('remaining_length_meter')
            ->limit(8)
            ->get();

        $upcomingBirthdays = \App\Models\Employee::upcomingBirthdays($branchId);

        return view('manager-ops.dashboard', compact(
            'installationsToday', 'pendingRelease', 'activeJobs', 'lowStock',
            'pendingInvoices', 'upcomingInstallations', 'stockAlerts',
            'upcomingBirthdays',
        ));
    }
}
