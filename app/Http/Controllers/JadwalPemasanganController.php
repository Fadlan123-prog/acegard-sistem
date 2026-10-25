<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerBuildingProduct;
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
        $isTeknisiOrKenek = $user->employee && $user->hasAnyRole(['teknisi', 'kenek']);
        $isTeknisi        = $user->hasRole('teknisi');

        // Teknisi / kenek only see their own schedule — cannot override via URL
        if ($isTeknisiOrKenek) {
            $tukangFilter = $user->employee->id;
        } else {
            $tukangFilter = $request->filled('tukang_id') ? (int) $request->tukang_id : null;
        }

        // Teknisi/kenek route tidak lewat middleware branch.access (lihat routes/web.php),
        // jadi session('active_branch_id') tidak pernah terisi untuk mereka. Untuk mereka,
        // JANGAN scope by branch_id sama sekali — job assignment (tukang_id/kenek_id/pivot
        // technicians) sudah cukup membatasi hasil, dan cabang penugasan bisa berbeda dari
        // cabang "rumah" karyawan (lihat TeknisiDashboardController yang juga tidak filter
        // branch). Menambah filter branch_id di sini akan diam-diam menyembunyikan order
        // lintas-cabang yang sah dari teknisi yang ditugaskan padanya.
        $activeBranchId = $this->branchContext->idOrNull();

        // ── Sales Orders (CRM pipeline) ───────────────────────────────
        $soQuery = SalesOrder::query()
            ->when($isTeknisiOrKenek, fn ($q) => $q, fn ($q) => $q->accessibleByUser())
            ->with(['tukang', 'kenek', 'marketing', 'technicians'])
            ->whereNotNull('scheduled_date')
            ->whereBetween('scheduled_date', [$startOfMonth, $endOfMonth])
            ->whereNotIn('status', ['cancelled']);

        if ($tukangFilter) {
            $soQuery->where(function ($q) use ($tukangFilter, $isTeknisi) {
                $q->where('tukang_id', $tukangFilter)
                  ->orWhere('kenek_id', $tukangFilter)
                  ->orWhere('marketing_id', $tukangFilter);
                // Gedung: penugasan lewat pivot technicians, kenek tidak berlaku di sana
                if ($isTeknisi) {
                    $q->orWhereHas('technicians', fn ($qt) => $qt->where('employees.id', $tukangFilter));
                }
            });
        }

        $salesOrders = $soQuery->orderBy('scheduled_date')->get();

        // ── Customers with install_date (existing system) ─────────────
        $custQuery = Customer::query()
            ->when($isTeknisiOrKenek, fn ($q) => $q, fn ($q) => $q->accessibleByUser())
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
        // Teknisi/kenek: tidak discope by branch_id sama sekali (lihat alasan di atas pada
        // $activeBranchId) — tukangFilter di bawah sudah wajib membatasi ke penugasan sendiri.
        $ppfQuery = CustomerPpfInstallation::with(['customerPpf', 'tukang', 'kenek', 'invoice'])
            ->whereNotNull('install_date')
            ->whereBetween('install_date', [$startOfMonth, $endOfMonth]);

        if (! $isTeknisiOrKenek && ! $user->hasRole('superadmin')) {
            $ppfQuery->whereHas('customerPpf', fn ($q) => $q->where('branch_id', $activeBranchId));
        }

        if ($tukangFilter) {
            $ppfQuery->where(fn ($q) =>
                $q->where('tukang_id', $tukangFilter)
                  ->orWhere('kenek_id', $tukangFilter)
            );
        }

        $ppfInstallations = $ppfQuery->orderBy('install_date')->get();

        // ── Customer Building (Kaca Film Gedung) ───────────────────────
        // customer_building has no install_date/tukang_id of its own — only
        // customer_building_product.warantee_start, and assignment after
        // conversion isn't tracked per employee at all (building jobs use the
        // SalesOrder technicians pivot only, which isn't carried over). So
        // this type is branch-scoped like the others but never filtered by
        // $tukangFilter, and is left out of teknisi/kenek's own schedule
        // entirely rather than guessing an ownership link that doesn't exist.
        $buildingProducts = collect();
        if (! $isTeknisiOrKenek) {
            $buildingQuery = CustomerBuildingProduct::with('customer.branch')
                ->whereNotNull('warantee_start')
                ->whereBetween('warantee_start', [$startOfMonth, $endOfMonth]);

            if (! $user->hasRole('superadmin')) {
                $buildingQuery->whereHas('customer', fn ($q) => $q->where('branch_id', $activeBranchId));
            }

            $buildingProducts = $buildingQuery->orderBy('warantee_start')->get();
        }

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
                // Teknisi/kenek tidak boleh masuk sales-orders.show (rute khusus role CRM) —
                // arahkan ke SPK publik kalau sudah terbit, kalau belum jadikan tak bisa diklik.
                'route' => $isTeknisiOrKenek
                    ? ($so->spk_public_url ?? '#')
                    : route('sales-orders.show', $so),
                'staff' => $so->assignsMultipleTechnicians()
                    ? $so->technicians->pluck('name')->join(' / ')
                    : collect([$so->tukang?->name, $so->kenek?->name])->filter()->join(' / '),
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
                // Teknisi/kenek tidak boleh masuk customer.edit (staff-only) — arahkan ke
                // halaman order mereka sendiri, yang sudah memvalidasi kepemilikan job ini.
                'route' => $isTeknisiOrKenek ? route('teknisi.orders.show', $c) : route('customer.edit', $c),
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
                // Belum ada halaman detail PPF khusus teknisi — nonaktifkan link-nya
                // daripada mengarah ke halaman staff yang akan menolak akses mereka.
                'route' => $isTeknisiOrKenek ? '#' : route('ppf-customers.show', $ppf->customer_ppf_id),
                'staff' => collect([$ppf->tukang?->name, $ppf->kenek?->name])->filter()->join(' / '),
                'color' => 'warning',
            ];
        }

        foreach ($buildingProducts as $bp) {
            $day = $bp->warantee_start->day;
            $events[$day][] = [
                'type'  => 'customer_building',
                'item'  => $bp,
                'date'  => $bp->warantee_start,
                'label' => $bp->customer?->name ?? '—',
                'sub'   => 'Kaca Film Bangunan',
                'route' => route('customer.building.edit', $bp->customer_building_id),
                'staff' => '',
                'color' => 'info',
            ];
        }

        // ── Staff list for filter (dipakai dropdown admin; teknisi/kenek tidak melihatnya) ──
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
        $totalEvents = $salesOrders->count() + $customers->count() + $ppfInstallations->count() + $buildingProducts->count();

        return view('jadwal-pemasangan.index', compact(
            'year', 'month', 'startOfMonth', 'events',
            'salesOrders', 'customers', 'ppfInstallations', 'buildingProducts',
            'employees', 'tukangFilter', 'totalEvents'
        ));
    }
}
