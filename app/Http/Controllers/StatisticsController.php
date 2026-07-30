<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\BranchContext;
use App\Services\RevenueQueryService;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function __construct(
        private RevenueQueryService $revenue,
        private BranchContext $branch,
    ) {}

    public function index(Request $request)
    {
        $user         = auth()->user();
        $isSuperadmin = $user->hasRole('superadmin');

        $selectedYear = (int) $request->get('year', now()->year);

        $selectedBranchId = $isSuperadmin
            ? ($request->filled('branch_id') ? (int) $request->get('branch_id') : null)
            : $this->branch->idOrNull();

        $branches = $isSuperadmin
            ? Branch::orderBy('name')->get(['id', 'name', 'city'])
            : collect();

        return view('statistics.index', [
            'title'            => 'Statistik Penjualan',
            'years'            => $this->revenue->availableYears(),
            'selectedYear'     => $selectedYear,
            'selectedBranchId' => $selectedBranchId,
            'isSuperadmin'     => $isSuperadmin,
            'branches'         => $branches,
            'monthly'          => $this->revenue->monthly($selectedYear, $selectedBranchId),
            'yearly'           => $this->revenue->yearly($selectedBranchId),
            'summary'          => $this->revenue->summary($selectedYear, $selectedBranchId),
        ]);
    }
}
