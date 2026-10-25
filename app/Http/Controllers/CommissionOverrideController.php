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

    /**
     * Override rules can legitimately span branches (a Branch A marketing
     * referral triggering an override for a Branch B installer) — so unlike
     * other controllers we don't reject cross-branch rules outright. We do
     * require the acting branch-scoped admin to have a real connection to
     * the rule via at least one side of it, otherwise a Branch A admin could
     * create/edit/delete override rules between two entirely unrelated
     * branches they have no legitimate reason to touch (audit finding M-8).
     */
    private function authorizeOverrideBranch(int $overrideEmployeeId, int $triggerMarketingId): void
    {
        $branchId = $this->branch->idOrNull();
        if ($branchId === null) {
            return; // superadmin
        }

        $touchesOwnBranch = Employee::whereIn('id', [$overrideEmployeeId, $triggerMarketingId])
            ->where('branch_id', $branchId)
            ->exists();

        abort_unless($touchesOwnBranch, 403, 'Aturan ini tidak melibatkan karyawan dari cabang Anda.');
    }

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

        $this->authorizeOverrideBranch((int) $data['override_employee_id'], (int) $data['trigger_marketing_id']);

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
        $this->authorizeOverrideBranch((int) $trigger->override_employee_id, (int) $trigger->trigger_marketing_id);

        $trigger->update(['is_active' => !$trigger->is_active]);

        $status = $trigger->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Aturan override berhasil {$status}.");
    }

    public function updateRate(Request $request, CommissionOverrideTrigger $trigger)
    {
        $this->authorizeOverrideBranch((int) $trigger->override_employee_id, (int) $trigger->trigger_marketing_id);

        $data = $request->validate([
            'rate' => ['required', 'numeric', 'min:0.01', 'max:100'],
        ]);

        $trigger->update(['rate' => $data['rate'] / 100]);

        return back()->with('success', 'Rate berhasil diperbarui.');
    }

    public function destroy(CommissionOverrideTrigger $trigger)
    {
        $this->authorizeOverrideBranch((int) $trigger->override_employee_id, (int) $trigger->trigger_marketing_id);

        $trigger->delete();

        return back()->with('success', 'Aturan override berhasil dihapus.');
    }
}
