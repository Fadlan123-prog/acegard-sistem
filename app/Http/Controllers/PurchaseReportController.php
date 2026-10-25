<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Supplier;
use App\Repositories\PurchaseRepository;
use App\Services\BranchContext;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PurchaseReportController extends Controller
{
    public function __construct(
        private BranchContext      $branch,
        private PurchaseRepository $repo,
    ) {}

    public function monthly(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end   = Carbon::parse($month . '-01')->endOfMonth();

        $user = auth()->user();

        // Branch access: non-superadmin is locked to their own branch.
        $filters = [
            'supplier_id' => $request->input('supplier_id'),
            'status'      => $request->input('status'),
        ];

        if (!$user->hasRole('superadmin')) {
            $filters['branch_id'] = $this->branch->id();
        } elseif ($request->filled('branch_id')) {
            $filters['branch_id'] = $request->input('branch_id');
        }

        $summary    = $this->repo->monthlySummary($start, $end, $filters);
        $details    = $this->repo->monthlyDetails($start, $end, $filters);
        $totalQty   = $summary->sum('total_qty');
        $grandTotal = $summary->sum('total_amount');

        $branches  = Branch::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.reports.monthly', compact(
            'month', 'summary', 'details', 'totalQty', 'grandTotal', 'branches', 'suppliers'
        ));
    }
}
