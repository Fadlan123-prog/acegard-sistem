<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CommissionOverrideTrigger;
use App\Models\Employee;
use App\Services\BranchContext;
use Illuminate\Http\Request;

class CommissionOverrideController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function index()
    {
        $branchId = $this->branch->idOrNull();
        $branch   = $branchId ? Branch::find($branchId) : null;

        // Filter aturan berdasarkan cabang override_employee
        $triggers = CommissionOverrideTrigger::with(['overrideEmployee', 'triggerMarketing'])
            ->when($branchId, fn($q) => $q->whereHas('overrideEmployee', fn($eq) => $eq->where('branch_id', $branchId)))
            ->orderBy('override_employee_id')
            ->orderBy('trigger_marketing_id')
            ->get()
            ->groupBy('override_employee_id');

        // Semua karyawan aktif — tidak difilter cabang karena employee override bisa lintas cabang
        $employees  = Employee::where('is_active', true)
            ->orderBy('name')->get();

        // Marketing: tampilkan semua cabang (marketing bisa lintas cabang)
        $marketings = Employee::where('job_position', 'Marketing')->where('is_active', true)->orderBy('name')->get();

        return view('commission.override-settings', compact(
            'triggers', 'employees', 'marketings', 'branch'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'override_employee_id' => ['required', 'exists:employees,id'],
            'trigger_marketing_id' => ['required', 'exists:employees,id'],
            'rate'                 => ['required', 'numeric', 'min:0.01', 'max:100'],
        ]);

        $rate = $data['rate'] / 100; // konversi % ke desimal

        $exists = CommissionOverrideTrigger::where('override_employee_id', $data['override_employee_id'])
            ->where('trigger_marketing_id', $data['trigger_marketing_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Aturan untuk kombinasi karyawan + marketing ini sudah ada.');
        }

        CommissionOverrideTrigger::create([
            'override_employee_id' => $data['override_employee_id'],
            'trigger_marketing_id' => $data['trigger_marketing_id'],
            'rate'                 => $rate,
            'is_active'            => true,
            'created_by'           => auth()->id(),
        ]);

        return back()->with('success', 'Aturan override komisi berhasil ditambahkan.');
    }

    public function toggleActive(CommissionOverrideTrigger $trigger)
    {
        $trigger->update(['is_active' => !$trigger->is_active]);

        $status = $trigger->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Aturan override berhasil {$status}.");
    }

    public function updateRate(Request $request, CommissionOverrideTrigger $trigger)
    {
        $data = $request->validate([
            'rate' => ['required', 'numeric', 'min:0.01', 'max:100'],
        ]);

        $trigger->update(['rate' => $data['rate'] / 100]);

        return back()->with('success', 'Rate berhasil diperbarui.');
    }

    public function destroy(CommissionOverrideTrigger $trigger)
    {
        $trigger->delete();

        return back()->with('success', 'Aturan override berhasil dihapus.');
    }
}
