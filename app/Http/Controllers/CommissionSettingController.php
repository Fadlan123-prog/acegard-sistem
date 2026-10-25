<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Services\BranchContext;
use Illuminate\Http\Request;

/**
 * Setting komisi per-karyawan: Sistem Saat Ini (fixed rate mobil / persentase
 * PPF yang sudah ada), Persentase Kustom (timpa dengan % dari harga invoice),
 * atau Tidak Dapat Komisi (baris komisi tetap dibuat, nominal dipaksa Rp0).
 * Diterapkan di EmployeeCommissionResolver, dipanggil dari CommissionReleaseService
 * (mobil) dan CommissionService::createPpfCommissions (PPF) saat komisi dirilis.
 */
class CommissionSettingController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function index()
    {
        $branchId = $this->branch->idOrNull();
        $branch   = $branchId ? Branch::find($branchId) : null;

        $employees = Employee::where('is_active', true)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get();

        return view('commission.employee-settings', compact('employees', 'branch'));
    }

    public function update(Request $request, Employee $employee)
    {
        $this->authorizeBranch($employee);

        $data = $request->validate([
            'commission_mode'       => ['required', 'in:default,percentage,none'],
            'commission_percentage' => ['required_if:commission_mode,percentage', 'nullable', 'numeric', 'min:0.01', 'max:100'],
        ]);

        $employee->update([
            'commission_mode'       => $data['commission_mode'],
            'commission_percentage' => $data['commission_mode'] === 'percentage'
                ? $data['commission_percentage'] / 100
                : null,
        ]);

        return back()->with('success', "Setting komisi {$employee->name} berhasil diperbarui.");
    }

    private function authorizeBranch(Employee $employee): void
    {
        $branchId = $this->branch->idOrNull();
        if ($branchId === null) {
            return; // superadmin
        }

        abort_unless((int) $employee->branch_id === (int) $branchId, 403);
    }
}
