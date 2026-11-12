<?php

namespace App\Http\Controllers;

use App\Services\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Pantau meter gedung per teknisi: berapa meter, kategori apa, komisinya berapa. */
class BuildingMeterReportController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function index(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());
        $branchId = $this->branch->idOrNull();

        // Hanya pekerjaan yang invoice-nya sudah lunas dalam rentang tanggal.
        $rows = DB::table('customer_building_technician_meters as m')
            ->join('customer_building as cb', 'cb.id', '=', 'm.customer_building_id')
            ->join('invoice_building as ib', 'ib.customer_building_id', '=', 'cb.id')
            ->join('employees as e', 'e.id', '=', 'm.employee_id')
            ->join('category_product_building as cat', 'cat.id', '=', 'm.category_product_building_id')
            ->whereNotNull('ib.pelunasan_at')
            ->whereBetween(DB::raw('DATE(ib.pelunasan_at)'), [$from, $to])
            ->when($branchId, fn ($q) => $q->where('cb.branch_id', $branchId))
            ->groupBy('e.id', 'e.name', 'cat.id', 'cat.name')
            ->orderBy('e.name')
            ->get([
                'e.id as employee_id', 'e.name as employee', 'cat.id as category_id', 'cat.name as category',
                DB::raw('SUM(m.meters) as meters'),
            ]);

        $rates = (array) config('commission.building_per_meter', []);

        $report = $rows->groupBy('employee_id')->map(function ($group) use ($rates) {
            $lines = $group->map(fn ($r) => [
                'category' => $r->category,
                'meters'   => (float) $r->meters,
                'rate'     => (int) ($rates[$r->category_id] ?? 0),
                'amount'   => (int) round((float) $r->meters * (int) ($rates[$r->category_id] ?? 0)),
            ]);

            return [
                'employee' => $group->first()->employee,
                'lines'    => $lines,
                'meters'   => $lines->sum('meters'),
                'amount'   => $lines->sum('amount'),
            ];
        })->sortByDesc('meters')->values();

        return view('commission.building-meters', [
            'title'  => 'Meter Teknisi Gedung',
            'report' => $report,
            'from'   => $from,
            'to'     => $to,
        ]);
    }
}
