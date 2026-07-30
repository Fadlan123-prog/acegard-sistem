<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseRepository
{
    /**
     * Aggregated summary by item name for a given date range and filters.
     * Filters: branch_id, supplier_id, status.
     */
    public function monthlySummary(Carbon $start, Carbon $end, array $filters): Collection
    {
        return $this->baseQuery($start, $end, $filters)
            ->select([
                DB::raw('COALESCE(pr.name, purchase_items.item_name) as item_name'),
                DB::raw('SUM(purchase_items.qty) as total_qty'),
                DB::raw('AVG(purchase_items.unit_price) as average_price'),
                DB::raw('SUM(purchase_items.total_price) as total_amount'),
                DB::raw('MIN(p.purchase_date) as first_purchase_date'),
                DB::raw('MAX(p.purchase_date) as last_purchase_date'),
                DB::raw('COUNT(DISTINCT p.id) as total_transactions'),
            ])
            ->groupBy(DB::raw('COALESCE(pr.name, purchase_items.item_name)'))
            ->orderBy('item_name')
            ->get();
    }

    /** Per-line detail rows for a given date range and filters. */
    public function monthlyDetails(Carbon $start, Carbon $end, array $filters): Collection
    {
        return $this->baseQuery($start, $end, $filters)
            ->select([
                'p.purchase_date', 'p.purchase_number',
                'b.name as branch_name', 's.name as supplier_name',
                DB::raw('COALESCE(pr.name, purchase_items.item_name) as item_name'),
                'purchase_items.qty', 'purchase_items.unit',
                'purchase_items.unit_price', 'purchase_items.total_price',
            ])
            ->orderBy('p.purchase_date')
            ->orderBy(DB::raw('COALESCE(pr.name, purchase_items.item_name)'))
            ->get();
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function baseQuery(Carbon $start, Carbon $end, array $filters)
    {
        $q = DB::table('purchase_items')
            ->join('purchases as p', 'p.id', '=', 'purchase_items.purchase_id')
            ->leftJoin('products as pr', 'pr.id', '=', 'purchase_items.product_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'p.supplier_id')
            ->leftJoin('branches as b', 'b.id', '=', 'p.branch_id')
            ->whereBetween('p.purchase_date', [
                $start->format('Y-m-d'),
                $end->format('Y-m-d'),
            ])
            ->where('p.status', '!=', 'cancelled');

        if (!empty($filters['branch_id'])) {
            $q->where('p.branch_id', $filters['branch_id']);
        }
        if (!empty($filters['supplier_id'])) {
            $q->where('p.supplier_id', $filters['supplier_id']);
        }
        if (!empty($filters['status'])) {
            $q->where('p.status', $filters['status']);
        }

        return $q;
    }
}
