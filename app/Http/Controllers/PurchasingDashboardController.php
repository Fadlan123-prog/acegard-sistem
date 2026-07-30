<?php

namespace App\Http\Controllers;

use App\Models\FilmStockPool;
use App\Services\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasingDashboardController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function dashboard(Request $request)
    {
        $branchId = $this->branch->idOrNull();

        // KPI stats
        $purchasesThisMonth = DB::table('purchases')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereMonth('purchase_date', now()->month)
            ->whereYear('purchase_date', now()->year)
            ->sum('total_amount');

        $purchasesCount = DB::table('purchases')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereMonth('purchase_date', now()->month)
            ->whereYear('purchase_date', now()->year)
            ->count();

        $lowFilmStock = FilmStockPool::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('remaining_length_meter', '<=', 10)
            ->count();

        $lowSupportingGoods = DB::table('supporting_goods')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereRaw('current_stock <= minimum_stock')
            ->where('minimum_stock', '>', 0)
            ->count();

        // Recent purchases
        $recentPurchases = DB::table('purchases')
            ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->when($branchId, fn ($q) => $q->where('purchases.branch_id', $branchId))
            ->select('purchases.*', 'suppliers.name as supplier_name')
            ->orderByDesc('purchase_date')
            ->limit(10)
            ->get();

        // Low film stock
        $stockAlerts = FilmStockPool::with(['branch'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('remaining_length_meter', '<=', 10)
            ->orderBy('remaining_length_meter')
            ->limit(10)
            ->get();

        // Low supporting goods
        $lowGoods = DB::table('supporting_goods')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereRaw('current_stock <= minimum_stock')
            ->where('minimum_stock', '>', 0)
            ->orderBy('current_stock')
            ->limit(8)
            ->get();

        return view('purchasing.dashboard', compact(
            'purchasesThisMonth', 'purchasesCount',
            'lowFilmStock', 'lowSupportingGoods',
            'recentPurchases', 'stockAlerts', 'lowGoods',
        ));
    }
}
