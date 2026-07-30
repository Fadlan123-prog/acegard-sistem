<?php

namespace App\Http\Controllers;

use App\Domain\HR\OffQuotaPolicy;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyLeaveRequestController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;

        if (! $employee) {
            return view('my-leave-requests.index', [
                'requests'   => collect(),
                'employee'   => null,
                'typeLabels' => LeaveRequest::$typeLabels,
                'noEmployee' => true,
            ]);
        }

        $requests = LeaveRequest::with(['approver'])
            ->where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        $liburQuota = $this->liburQuotaInfo($employee);

        return view('my-leave-requests.index', [
            'requests'   => $requests,
            'employee'   => $employee,
            'typeLabels' => LeaveRequest::$typeLabels,
            'noEmployee' => false,
            'liburQuota' => $liburQuota,
        ]);
    }

    public function create()
    {
        $employee = Auth::user()->employee;

        if (! $employee) {
            return redirect()->route('my-leave-requests.index')
                ->with('error', 'Akun Anda belum terhubung ke data karyawan. Hubungi admin.');
        }

        $liburQuota = $this->liburQuotaInfo($employee);

        return view('my-leave-requests.create', [
            'employee'   => $employee,
            'typeLabels' => LeaveRequest::$typeLabels,
            'liburQuota' => $liburQuota,
        ]);
    }

    public function store(Request $request)
    {
        $employee = Auth::user()->employee;

        if (! $employee) {
            return back()->with('error', 'Akun Anda belum terhubung ke data karyawan.');
        }

        $validated = $request->validate([
            'leave_type' => 'required|in:annual,sick,permission,emergency,libur',
            'from_date'  => 'required|date|after_or_equal:today',
            'to_date'    => 'required|date|after_or_equal:from_date',
            'reason'     => 'required|string|max:500',
        ], [
            'leave_type.required'     => 'Jenis cuti wajib dipilih.',
            'from_date.required'      => 'Tanggal mulai wajib diisi.',
            'from_date.after_or_equal'=> 'Tanggal mulai tidak boleh di masa lalu.',
            'to_date.required'        => 'Tanggal selesai wajib diisi.',
            'to_date.after_or_equal'  => 'Tanggal selesai harus setelah tanggal mulai.',
            'reason.required'         => 'Alasan wajib diisi.',
        ]);

        $from = Carbon::parse($validated['from_date']);
        $to   = Carbon::parse($validated['to_date']);
        $days = $from->diffInDays($to) + 1;

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

        return redirect()->route('my-leave-requests.index')
            ->with('success', 'Pengajuan berhasil dikirim. Menunggu persetujuan admin.');
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $employee = Auth::user()->employee;

        if (! $employee || $leaveRequest->employee_id !== $employee->id) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        $leaveRequest->load(['approver']);

        return view('my-leave-requests.show', [
            'leaveRequest' => $leaveRequest,
            'typeLabels'   => LeaveRequest::$typeLabels,
        ]);
    }

    private function liburQuotaInfo($employee): array
    {
        $quota = OffQuotaPolicy::forPosition((string) $employee->job_position);

        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth   = now()->endOfMonth()->toDateString();

        $used = Attendance::where('employee_id', $employee->id)
            ->where('status', 'off')
            ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
            ->count();

        return [
            'quota'     => $quota,
            'used'      => $used,
            'remaining' => max(0, $quota - $used),
            'month'     => now()->translatedFormat('F Y'),
        ];
    }

    public function cancel(LeaveRequest $leaveRequest)
    {
        $employee = Auth::user()->employee;

        if (! $employee || $leaveRequest->employee_id !== $employee->id) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        if (! $leaveRequest->isPending()) {
            return back()->with('error', 'Hanya pengajuan pending yang dapat dibatalkan.');
        }

        $leaveRequest->update(['status' => 'cancelled']);

        return redirect()->route('my-leave-requests.index')
            ->with('success', 'Pengajuan berhasil dibatalkan.');
    }
}
