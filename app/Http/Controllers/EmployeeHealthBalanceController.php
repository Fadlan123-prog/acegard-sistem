<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeHealthBalance;
use Illuminate\Http\Request;

class EmployeeHealthBalanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $year = (int) ($request->get('year') ?: now()->year);

        $query = Employee::query()
            ->whereIn('job_position', ['Teknisi', 'Kenek'])
            ->where('is_active', true)
            ->with('branch');

        if (!$user->hasRole('superadmin')) {
            $allowedBranchIds = $user->branches()->pluck('branches.id')->all();
            $query->whereIn('branch_id', $allowedBranchIds);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $employees = $query->orderBy('name')->get();

        // Self-healing: every active teknisi/kenek should have a balance row for
        // the selected year, even if claims:reset-health-balances hasn't run yet
        // or the employee was hired after the last reset.
        foreach ($employees as $employee) {
            EmployeeHealthBalance::firstOrCreate(
                ['employee_id' => $employee->id, 'year' => $year],
                ['allocated_amount' => config('hr.default_health_allocation'), 'used_amount' => 0]
            );
        }

        $balances = EmployeeHealthBalance::where('year', $year)
            ->whereIn('employee_id', $employees->pluck('id'))
            ->get()
            ->keyBy('employee_id');

        $branches = $user->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : $user->branches()->orderBy('name')->get();

        return view('employee-health-balances.index', compact('employees', 'balances', 'year', 'branches'));
    }

    public function update(Request $request, EmployeeHealthBalance $employeeHealthBalance)
    {
        $user = $request->user();

        if (!$user->hasRole('superadmin')) {
            $allowedBranchIds = $user->branches()->pluck('branches.id')->all();
            abort_unless(in_array($employeeHealthBalance->employee->branch_id, $allowedBranchIds), 403);
        }

        $data = $request->validate([
            'allocated_amount' => ['required', 'integer', 'min:0'],
        ]);

        $employeeHealthBalance->update($data);

        return back()->with('success', 'Alokasi saldo kesehatan berhasil diperbarui.');
    }
}
