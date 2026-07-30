<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPpfInstallation;
use App\Models\Employee;
use App\Models\SalesOrder;
use App\Services\BranchContext;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JadwalPemasanganController extends Controller
{
    public function __construct(private BranchContext $branchContext) {}

    public function index(Request $request)
    {
        $year  = (int) $request->get('year',  now()->year);
        $month = (int) $request->get('month', now()->month);

        // Clamp to valid range
        $year  = max(2020, min(2030, $year));
        $month = max(1, min(12, $month));

        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        $user = auth()->user();

        // Teknisi / kenek only see their own schedule — cannot override via URL
        if ($user->employee && $user->hasAnyRole(['teknisi', 'kenek'])) {
            $tukangFilter = $user->employee->id;
        } else {
            $tukangFilter = $request->filled('tukang_id') ? (int) $request->tukang_id : null;
        }

        // ── Sales Orders (CRM pipeline) ───────────────────────────────
        $soQuery = SalesOrder::accessibleByUser()
            ->with(['tukang', 'kenek', 'marketing'])
            ->whereNotNull('scheduled_date')
            ->whereBetween('scheduled_date', [$startOfMonth, $endOfMonth])
            ->whereNotIn('status', ['cancelled']);

        if ($tukangFilter) {
            $soQuery->where(fn ($q) =>
                $q->where('tukang_id', $tukangFilter)
                  ->orWhere('kenek_id', $tukangFilter)
                  ->orWhere('marketing_id', $tukangFilter)
            );
        }

        $salesOrders = $soQuery->orderBy('scheduled_date')->get();

        // ── Customers with install_date (existing system) ─────────────
        $custQuery = Customer::accessibleByUser()
            ->with(['tukang', 'kenek', 'marketing', 'latestInvoice'])
            ->whereNotNull('install_date')
            ->whereBetween('install_date', [$startOfMonth, $endOfMonth]);

        if ($tukangFilter) {
            $custQuery->where(fn ($q) =>
                $q->where('tukang_id', $tukangFilter)
                  ->orWhere('kenek_id', $tukangFilter)
            );
        }

        $customers = $custQuery->orderBy('install_date')->get();

        // ── PPF Installations ─────────────────────────────────────────
        $ppfQuery = CustomerPpfInstallation::with(['customerPpf', 'tukang', 'kenek', 'invoice'])
            ->whereHas('customerPpf', fn ($q) =>
                $q->where('branch_id', $this->branchContext->idOrNull())
            )
            ->whereNotNull('install_date')
            ->whereBetween('install_date', [$startOfMonth, $endOfMonth]);

        if (auth()->user()->hasRole('superadmin')) {
            $ppfQuery = CustomerPpfInstallation::with(['customerPpf', 'tukang', 'kenek', 'invoice'])
                ->whereNotNull('install_date')
                ->whereBetween('install_date', [$startOfMonth, $endOfMonth]);
        }

        if ($tukangFilter) {
            $ppfQuery->where(fn ($q) =>
                $q->where('tukang_id', $tukangFilter)
                  ->orWhere('kenek_id', $tukangFilter)
            );
        }

        $ppfInstallations = $ppfQuery->orderBy('install_date')->get();

        // ── Build events map keyed by day ─────────────────────────────
        $events = [];

        foreach ($salesOrders as $so) {
            $day = $so->scheduled_date->day;
            $events[$day][] = [
                'type'  => 'so',
                'item'  => $so,
                'date'  => $so->scheduled_date,
                'label' => $so->prospect_name,
                'sub'   => $so->so_number,
                'route' => route('sales-orders.show', $so),
                'staff' => collect([$so->tukang?->name, $so->kenek?->name])->filter()->join(' / '),
                'color' => 'success',
            ];
        }

        foreach ($customers as $c) {
            $day = $c->install_date->day;
            $events[$day][] = [
                'type'  => 'customer',
                'item'  => $c,
                'date'  => $c->install_date,
                'label' => $c->name,
                'sub'   => trim("{$c->vehicle_brand} {$c->vehicle_model}"),
                'route' => route('customer.edit', $c),
                'staff' => collect([$c->tukang?->name, $c->kenek?->name])->filter()->join(' / '),
                'color' => 'primary',
            ];
        }

        foreach ($ppfInstallations as $ppf) {
            $day = $ppf->install_date->day;
            $events[$day][] = [
                'type'  => 'ppf',
                'item'  => $ppf,
                'date'  => $ppf->install_date,
                'label' => $ppf->customerPpf?->name ?? '—',
                'sub'   => 'PPF',
                'route' => route('ppf-customers.show', $ppf->customer_ppf_id),
                'staff' => collect([$ppf->tukang?->name, $ppf->kenek?->name])->filter()->join(' / '),
                'color' => 'warning',
            ];
        }

        // ── Staff list for filter ─────────────────────────────────────
        $activeBranchId = $this->branchContext->idOrNull();
        $employees = Employee::where(function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId)->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if (auth()->user()->hasRole('superadmin')) {
            $employees = Employee::where('is_active', true)->orderBy('name')->get();
        }

        // Totals
        $totalEvents = $salesOrders->count() + $customers->count() + $ppfInstallations->count();

        return view('jadwal-pemasangan.index', compact(
            'year', 'month', 'startOfMonth', 'events',
            'salesOrders', 'customers', 'ppfInstallations',
            'employees', 'tukangFilter', 'totalEvents'
        ));
    }
}
