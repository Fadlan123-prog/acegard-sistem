<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Shift;
use App\Services\BranchContext;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function __construct(private readonly BranchContext $branch) {}

    public function index()
    {
        $branchId = $this->branch->id();

        $shifts = Shift::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with('branch')
            ->orderBy('name')
            ->get();

        return view('shifts.index', compact('shifts'));
    }

    public function create()
    {
        $branches = Branch::where('status', 'active')->when(! auth()->user()->hasRole('superadmin'), fn ($q) => $q->whereIn('id', auth()->user()->accessibleBranchIds()))->orderBy('name')->get();
        return view('shifts.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id'              => 'nullable|exists:branches,id',
            'name'                   => 'required|string|max:100',
            'start_time'             => 'required|date_format:H:i',
            'end_time'               => 'required|date_format:H:i',
            'overtime_after_minutes' => 'required|integer|min:0|max:300',
            'is_active'              => 'boolean',
        ]);

        $this->authorizeBranchInput($validated['branch_id'] ?? null);

        $validated['is_active'] = $request->boolean('is_active', true);

        Shift::create($validated);

        return redirect()->route('shifts.index')->with('success', 'Shift berhasil ditambahkan.');
    }

    public function edit(Shift $shift)
    {
        $this->authorizeShift($shift);
        $branches = Branch::where('status', 'active')->when(! auth()->user()->hasRole('superadmin'), fn ($q) => $q->whereIn('id', auth()->user()->accessibleBranchIds()))->orderBy('name')->get();
        return view('shifts.edit', compact('shift', 'branches'));
    }

    public function update(Request $request, Shift $shift)
    {
        $this->authorizeShift($shift);
        $validated = $request->validate([
            'branch_id'              => 'nullable|exists:branches,id',
            'name'                   => 'required|string|max:100',
            'start_time'             => 'required|date_format:H:i',
            'end_time'               => 'required|date_format:H:i',
            'overtime_after_minutes' => 'required|integer|min:0|max:300',
            'is_active'              => 'boolean',
        ]);

        $this->authorizeBranchInput($validated['branch_id'] ?? null);

        $validated['is_active'] = $request->boolean('is_active', false);

        $shift->update($validated);

        return redirect()->route('shifts.index')->with('success', 'Shift berhasil diperbarui.');
    }

    public function destroy(Shift $shift)
    {
        $this->authorizeShift($shift);
        if ($shift->attendances()->exists()) {
            return back()->with('error', 'Shift tidak dapat dihapus karena sudah digunakan di absensi.');
        }

        $shift->delete();

        return redirect()->route('shifts.index')->with('success', 'Shift berhasil dihapus.');
    }

    /** Shift global (branch_id null) hanya boleh diubah superadmin. */
    private function authorizeShift(Shift $shift): void
    {
        $this->authorizeBranchInput($shift->branch_id);
    }

    private function authorizeBranchInput($branchId): void
    {
        $user = auth()->user();

        if ($user->hasRole('superadmin')) {
            return;
        }

        abort_unless($branchId !== null && $user->canAccessBranch((int) $branchId), 403, 'Bukan cabang Anda.');
    }
}
