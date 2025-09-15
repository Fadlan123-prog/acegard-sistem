<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('superadmin')) {

            // ----- Rekap per KATEGORI (yang sudah ada) -----
            $catQuery = DB::table('customer_building_product as cbp')
                ->join('category_product_building as cat', 'cat.id', '=', 'cbp.category_product_building_id');

            $rows = $catQuery
                ->groupBy('cat.id','cat.name')
                ->orderBy('cat.name')
                ->select([
                    'cat.id',
                    'cat.name',
                    DB::raw('SUM(cbp.meters)  AS total_meters'),
                    DB::raw('COUNT(*)         AS total_rows'),
                ])
                ->get();

            $grandTotal = $rows->sum('total_meters');

            // ----- Rekap per PRODUK BUILDING -----
            $prodQuery = DB::table('customer_building_product as cbp')
                ->join('product_building as p', 'p.id', '=', 'cbp.product_building_id')
                ->join('category_product_building as c', 'c.id', '=', 'p.category_product_building_id');


            $products = $prodQuery
                ->groupBy('p.id','p.name','c.name')
                ->orderBy('c.name')
                ->orderBy('p.name')
                ->select([
                    'p.id',
                    'p.name',
                    'c.name as category_name',
                    DB::raw('SUM(cbp.meters) AS total_meters'),
                    DB::raw('COUNT(*)        AS total_rows'),
                ])
                ->get();

            return view('dashboard.index', [
                'title'         => 'Dashboard Superadmin',
                'totalUsers'    => \App\Models\User::count(),
                'totalCustomers'=> \App\Models\Customer::count(),
                'totalBranches' => \App\Models\Branch::count(),
                'rows'          => $rows,        // rekap kategori
                'grandTotal'    => $grandTotal,
                'products'      => $products,
            ]);
        }

        if ($user->hasRole('admin')) {
            $activeBranchId = session('active_branch_id') ?? $user->branches->first()->id ?? null;

            return view('dashboard.index', [
                'title'     => 'Dashboard Cabang',
                'branch'    => \App\Models\Branch::find($activeBranchId),
                'customers' => \App\Models\Customer::where('branch_id', $activeBranchId)->get(),
                'filters'   => ['from'=>$request->from, 'to'=>$request->to],
            ]);
        }

        abort(403, 'Akses ditolak.');
    }
}
