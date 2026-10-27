<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\SalesTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;
        abort_unless($employee, 403, 'Akun ini tidak terhubung ke data karyawan.');

        $profile = $employee->salesNetworkProfile;

        $myClosings = SalesTransaction::where('sales_employee_id', $employee->id)
            ->where('status', 'paid')
            ->count();

        $myClosingValue = (int) SalesTransaction::where('sales_employee_id', $employee->id)
            ->where('status', 'paid')
            ->sum('total_price');

        $myCommissionEligible = (int) Commission::where('employees_id', $employee->id)
            ->whereIn('role', array_keys(Commission::$salesRoleLabels))
            ->where('status', 'eligible')
            ->sum('amount');

        $myCommissionPaid = (int) Commission::where('employees_id', $employee->id)
            ->whereIn('role', array_keys(Commission::$salesRoleLabels))
            ->where('status', 'paid')
            ->sum('amount');

        $recentTransactions = SalesTransaction::where('sales_employee_id', $employee->id)
            ->latest()
            ->limit(10)
            ->get();

        $commissionBreakdown = Commission::where('employees_id', $employee->id)
            ->whereIn('role', array_keys(Commission::$salesRoleLabels))
            ->selectRaw('role, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('role')
            ->get();

        $recruitCount = $employee->sponsoredRecruits()->count();

        return view('sales-dashboard.index', compact(
            'profile', 'myClosings', 'myClosingValue',
            'myCommissionEligible', 'myCommissionPaid',
            'recentTransactions', 'commissionBreakdown', 'recruitCount',
        ));
    }
}
