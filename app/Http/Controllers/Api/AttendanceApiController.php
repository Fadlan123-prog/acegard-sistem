<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use App\Services\OvertimeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    public function __construct(private readonly OvertimeService $overtime) {}

    public function checkIn(Request $request): JsonResponse
    {
        $request->validate([
            'lat'   => 'required|numeric|between:-90,90',
            'lng'   => 'required|numeric|between:-180,180',
            'photo' => 'nullable|string|max:5000000', // base64
        ]);

        $employee = $this->resolveEmployee($request);
        if (!$employee) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $today = Carbon::today()->toDateString();

        $existing = Attendance::where('employee_id', $employee->id)
            ->where('attendance_date', $today)
            ->first();

        if ($existing && $existing->check_in_at) {
            return response()->json(['message' => 'Sudah check-in hari ini.'], 422);
        }

        $shift = Shift::where('branch_id', $employee->branch_id)
            ->where('is_active', true)
            ->first();

        $photoPath = null;
        if ($request->filled('photo')) {
            $photoPath = $this->storeBase64Photo($request->input('photo'), $employee->id);
        }

        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'attendance_date' => $today],
            [
                'branch_id'    => $employee->branch_id,
                'created_by'   => null,
                'shift_id'     => $shift?->id,
                'status'       => 'present',
                'daily_salary' => $this->dailySalary($employee),
                'check_in_at'  => now(),
                'check_in_lat' => $request->input('lat'),
                'check_in_lng' => $request->input('lng'),
                'check_in_photo' => $photoPath,
            ]
        );

        $this->overtime->calculate($attendance);

        return response()->json([
            'message'    => 'Check-in berhasil.',
            'check_in_at' => $attendance->check_in_at->toDateTimeString(),
            'shift'      => $shift ? ['name' => $shift->name, 'start' => $shift->start_time, 'end' => $shift->end_time] : null,
        ]);
    }

    public function checkOut(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $employee = $this->resolveEmployee($request);
        if (!$employee) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('attendance_date', $today)
            ->whereNotNull('check_in_at')
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'Belum check-in hari ini.'], 422);
        }

        if ($attendance->check_out_at) {
            return response()->json(['message' => 'Sudah check-out hari ini.'], 422);
        }

        $attendance->update([
            'check_out_at'  => now(),
            'check_out_lat' => $request->input('lat'),
            'check_out_lng' => $request->input('lng'),
        ]);

        $this->overtime->calculate($attendance->fresh());

        return response()->json([
            'message'      => 'Check-out berhasil.',
            'check_out_at'    => $attendance->check_out_at->toDateTimeString(),
            'overtime_minutes' => $attendance->fresh()->overtime_minutes,
        ]);
    }

    private function resolveEmployee(Request $request): ?Employee
    {
        $token = $request->bearerToken();
        if (!$token) return null;

        return Employee::where('mobile_token', $token)
            ->where('is_active', true)
            ->first();
    }

    private function dailySalary(Employee $employee): int
    {
        $monthly = (int) ($employee->monthly_salary ?? 0);
        $daysInMonth = now()->daysInMonth;
        return $daysInMonth > 0 ? (int) round($monthly / $daysInMonth) : 0;
    }

    private function storeBase64Photo(string $base64, int $employeeId): ?string
    {
        if (!preg_match('/^data:image\/(jpe?g|png|webp);base64,/i', $base64, $m)) {
            return null;
        }

        $ext     = strtolower($m[1]);
        $data    = substr($base64, strpos($base64, ',') + 1);
        $decoded = base64_decode($data, true);
        if ($decoded === false || @getimagesizefromstring($decoded) === false) {
            return null;
        }

        $dir  = public_path('storage/checkin-photos');
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = 'checkin_' . $employeeId . '_' . now()->format('Ymd_His') . '.' . $ext;
        file_put_contents($dir . '/' . $filename, $decoded);

        return 'storage/checkin-photos/' . $filename;
    }
}
