<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\BranchContext;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveRequestController extends Controller
{
    public function __construct(private readonly BranchContext $branch) {}

    public function index()
    {
        $branchId = $this->branch->id();

        $requests = LeaveRequest::with(['employee', 'branch', 'requester'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('leave-requests.index', compact('requests'));
    }

    public function create()
    {
        $branchId  = $this->branch->id();
        $employees = Employee::where('is_active', true)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get();

        $typeLabels = LeaveRequest::$typeLabels;

        return view('leave-requests.create', compact('employees', 'typeLabels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type'  => 'required|in:annual,sick,permission,emergency,libur',
            'from_date'   => 'required|date',
            'to_date'     => 'required|date|after_or_equal:from_date',
            'reason'      => 'required|string|max:500',
        ]);

        $employee  = Employee::findOrFail($validated['employee_id']);
        $from      = Carbon::parse($validated['from_date']);
        $to        = Carbon::parse($validated['to_date']);
        $days      = $from->diffInDays($to) + 1;

        LeaveRequest::create([
            'employee_id'  => $employee->id,
            'branch_id'    => $employee->branch_id,
            'requested_by' => Auth::id(),
            'leave_type'   => $validated['leave_type'],
            'from_date'    => $from->toDateString(),
            'to_date'      => $to->toDateString(),
            'days'         => $days,
            'reason'       => $validated['reason'],
            'status'       => 'pending',
        ]);

        return redirect()->route('leave-requests.index')->with('success', 'Pengajuan cuti berhasil dibuat.');
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['employee', 'branch', 'requester', 'approver']);
        return view('leave-requests.show', compact('leaveRequest'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        if (!$leaveRequest->isPending()) {
            return back()->with('error', 'Hanya pengajuan pending yang dapat disetujui.');
        }

        $request->validate(['approval_notes' => 'nullable|string|max:500']);

        DB::transaction(function () use ($leaveRequest, $request) {
            $leaveRequest->update([
                'status'         => 'approved',
                'approved_by'    => Auth::id(),
                'approval_notes' => $request->input('approval_notes'),
                'approved_at'    => now(),
            ]);

            // Write attendance rows for each day in the leave range
            $attendanceStatus = LeaveRequest::$typeToAttendanceStatus[$leaveRequest->leave_type];
            $period           = CarbonPeriod::create($leaveRequest->from_date, $leaveRequest->to_date);

            foreach ($period as $date) {
                $dailySalary = $this->dailySalary($leaveRequest->employee);

                Attendance::updateOrCreate(
                    [
                        'employee_id'     => $leaveRequest->employee_id,
                        'attendance_date' => $date->toDateString(),
                    ],
                    [
                        'branch_id'        => $leaveRequest->branch_id,
                        'created_by'       => Auth::id(),
                        'leave_request_id' => $leaveRequest->id,
                        'status'           => $attendanceStatus,
                        'daily_salary'     => $dailySalary,
                        'notes'            => LeaveRequest::$typeLabels[$leaveRequest->leave_type],
                    ]
                );
            }
        });

        return back()->with('success', 'Pengajuan cuti disetujui.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        if (!$leaveRequest->isPending()) {
            return back()->with('error', 'Hanya pengajuan pending yang dapat ditolak.');
        }

        $request->validate(['approval_notes' => 'nullable|string|max:500']);

        $leaveRequest->update([
            'status'         => 'rejected',
            'approved_by'    => Auth::id(),
            'approval_notes' => $request->input('approval_notes'),
            'approved_at'    => now(),
        ]);

        return back()->with('success', 'Pengajuan cuti ditolak.');
    }

    public function cancel(LeaveRequest $leaveRequest)
    {
        if (!$leaveRequest->isPending()) {
            return back()->with('error', 'Hanya pengajuan pending yang dapat dibatalkan.');
        }

        $leaveRequest->update(['status' => 'cancelled']);

        return back()->with('success', 'Pengajuan cuti dibatalkan.');
    }

    private function dailySalary(Employee $employee): int
    {
        $monthly    = (int) ($employee->monthly_salary ?? 0);
        $daysInMonth = now()->daysInMonth;
        return $daysInMonth > 0 ? (int) round($monthly / $daysInMonth) : 0;
    }
}
