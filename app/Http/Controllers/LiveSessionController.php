<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LiveSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LiveSessionController extends Controller
{
    private function branchId(Request $request): ?int
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['superadmin', 'owner', 'manager_marketing'])) {
            return $request->filled('branch_id') ? (int) $request->query('branch_id') : null;
        }

        return $user->employee->branch_id ?? null;
    }

    private function ownEmployeeId(Request $request): ?int
    {
        $user = auth()->user();

        return $user->hasAnyRole(['superadmin', 'owner', 'manager_marketing']) ? null : $user->employee_id;
    }

    public function index(Request $request)
    {
        $branchId   = $this->branchId($request);
        $employeeId = $this->ownEmployeeId($request);
        $month      = (int) $request->query('month', now()->month);
        $year       = (int) $request->query('year', now()->year);

        $sessions = LiveSession::with(['employee', 'branch'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($employeeId, fn ($q) => $q->where('employee_id', $employeeId))
            ->whereMonth('session_date', $month)
            ->whereYear('session_date', $year)
            ->orderByDesc('session_date')
            ->paginate(20)
            ->withQueryString();

        $statsBase = LiveSession::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($employeeId, fn ($q) => $q->where('employee_id', $employeeId))
            ->whereMonth('session_date', $month)
            ->whereYear('session_date', $year);

        $totalHours    = round((clone $statsBase)->sum('duration_minutes') / 60, 1);
        $totalSessions = (clone $statsBase)->count();
        $totalViewers  = (clone $statsBase)->sum('viewers_peak');
        $totalRevenue  = (clone $statsBase)->sum('revenue');

        $byHost = LiveSession::query()
            ->join('employees', 'live_sessions.employee_id', '=', 'employees.id')
            ->when($branchId, fn ($q) => $q->where('live_sessions.branch_id', $branchId))
            ->whereMonth('live_sessions.session_date', $month)
            ->whereYear('live_sessions.session_date', $year)
            ->select(
                'employees.id', 'employees.name',
                DB::raw('SUM(live_sessions.duration_minutes) as total_minutes'),
                DB::raw('SUM(live_sessions.leads_generated) as total_leads'),
                DB::raw('SUM(live_sessions.closing_count) as total_closing'),
            )
            ->groupBy('employees.id', 'employees.name')
            ->orderByDesc('total_minutes')
            ->get();

        return view('live-sessions.index', compact(
            'sessions', 'totalHours', 'totalSessions', 'totalViewers', 'totalRevenue',
            'byHost', 'month', 'year',
        ));
    }

    public function create()
    {
        $user = auth()->user();
        $employees = $user->hasAnyRole(['superadmin', 'owner', 'manager_marketing'])
            ? Employee::where('is_active', true)->orderBy('name')->get()
            : Employee::where('id', $user->employee_id)->get();

        return view('live-sessions.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'       => 'required|exists:employees,id',
            'session_date'      => 'required|date',
            'duration_minutes'  => 'required|integer|min:0',
            'viewers_peak'      => 'required|integer|min:0',
            'viewers_avg'       => 'required|integer|min:0',
            'chat_count'        => 'nullable|integer|min:0',
            'product_questions' => 'nullable|integer|min:0',
            'leads_generated'   => 'required|integer|min:0',
            'closing_count'     => 'nullable|integer|min:0',
            'revenue'           => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string|max:255',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $user     = auth()->user();

        if (! $user->hasAnyRole(['superadmin', 'owner', 'manager_marketing']) && (int) $employee->id !== (int) $user->employee_id) {
            abort(403, 'Hanya bisa mencatat sesi live atas nama diri sendiri.');
        }

        LiveSession::create([
            ...$validated,
            'branch_id'  => $employee->branch_id,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('live-sessions.index')->with('success', 'Sesi live berhasil dicatat.');
    }

    public function destroy(LiveSession $session)
    {
        $user = auth()->user();

        if (! $user->hasAnyRole(['superadmin', 'owner', 'manager_marketing']) && (int) $session->employee_id !== (int) $user->employee_id) {
            abort(403);
        }

        $session->delete();

        return back()->with('success', 'Sesi live dihapus.');
    }
}
