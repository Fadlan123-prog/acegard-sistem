<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Services\BranchContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;

class MealAllowanceController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    private array $presentStatuses = [
        'present',
        'hadir',
        'masuk',
        'late',
        'terlambat',
    ];

    public function weekly(Request $request)
    {
        $data = $this->buildWeeklyData($request);

        return view('meal-allowances.weekly', $data);
    }

    public function weeklyPdf(Request $request)
    {
        $data = $this->buildWeeklyData($request);

        $fileName = 'uang-makan-mingguan-' .
            $data['startDate']->format('Ymd') .
            '-' .
            $data['endDate']->format('Ymd') .
            '.pdf';

        $pdf = Pdf::loadView('meal-allowances.weekly-pdf', $data)
            ->setPaper('A4', 'landscape');

        return $pdf->stream($fileName);
    }

    private function buildWeeklyData(Request $request): array
    {
        $user = auth()->user();

        $branches = $user->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : $user->branches()->orderBy('name')->get();

        $branchId = $request->input('branch_id') ?? $this->branch->idOrNull();

        if (!$branchId && $branches->isNotEmpty()) {
            $branchId = $branches->first()->id;
        }

        if (!$user->hasRole('superadmin')) {
            $allowedBranchIds = $user->branches->pluck('id')->map(fn ($id) => (int) $id);

            if (!$allowedBranchIds->contains((int) $branchId)) {
                abort(403, 'Kamu tidak memiliki akses ke cabang ini.');
            }
        }

        if ($branchId) {
            session(['active_branch_id' => (int) $branchId]);
        }

        $branch = Branch::find($branchId);

        $selectedDate = $request->input('week_date', now()->format('Y-m-d'));

        $startDate = Carbon::parse($selectedDate)->startOfWeek(Carbon::MONDAY);
        $endDate   = Carbon::parse($selectedDate)->endOfWeek(Carbon::SUNDAY);

        $weekDays = collect(range(0, 6))->map(function ($day) use ($startDate) {
            $date = $startDate->copy()->addDays($day);

            return [
                'date'  => $date->format('Y-m-d'),
                'label' => $date->translatedFormat('l'),
                'short' => $date->translatedFormat('D'),
                'day'   => $date->format('d/m'),
            ];
        });

        $employeeIdsWithRecords = DB::table('attendances')
            ->where('branch_id', $branchId)
            ->whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->pluck('employee_id')
            ->unique();

        $employees = Employee::query()
            ->where('branch_id', $branchId)
            ->where(function ($q) use ($employeeIdsWithRecords) {
                $q->where('is_active', true)
                  ->orWhereIn('id', $employeeIdsWithRecords);
            })
            ->orderBy('job_position')
            ->orderBy('name')
            ->get();

        $attendances = DB::table('attendances')
            ->where('branch_id', $branchId)
            ->whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        $attendanceMap = [];

        foreach ($attendances as $attendance) {
            $date = Carbon::parse($attendance->attendance_date)->format('Y-m-d');
            $key = $attendance->employee_id . '|' . $date;

            $status = strtolower((string) ($attendance->status ?? ''));

            if (!isset($attendanceMap[$key])) {
                $attendanceMap[$key] = $status;
            } else {
                if ($this->isPresent($status)) {
                    $attendanceMap[$key] = $status;
                }
            }
        }

        $mealRows = $employees->map(function ($employee) use ($weekDays, $attendanceMap) {
            $dailyRate = $this->mealDailyRate($employee);

            $daily = [];
            $presentDays = 0;
            $absentDays = 0;

            foreach ($weekDays as $day) {
                $key = $employee->id . '|' . $day['date'];
                $status = $attendanceMap[$key] ?? null;

                $isPresent = $this->isPresent($status);

                if ($isPresent) {
                    $presentDays++;
                } else {
                    $absentDays++;
                }

                $daily[$day['date']] = [
                    'status'     => $status,
                    'is_present' => $isPresent,
                ];
            }

            $totalAllowance = $presentDays * $dailyRate;

            return [
                'employee'        => $employee,
                'job_position'    => $employee->job_position,
                'daily_rate'      => $dailyRate,
                'daily'           => $daily,
                'present_days'    => $presentDays,
                'absent_days'     => $absentDays,
                'total_allowance' => $totalAllowance,
            ];
        });

        $totalPresentDays = $mealRows->sum('present_days');
        $totalAbsentDays = $mealRows->sum('absent_days');
        $grandTotal = $mealRows->sum('total_allowance');

        return compact(
            'branches',
            'branch',
            'branchId',
            'selectedDate',
            'startDate',
            'endDate',
            'weekDays',
            'mealRows',
            'totalPresentDays',
            'totalAbsentDays',
            'grandTotal'
        );
    }

    private function mealDailyRate($employee): int
    {
        if (!(bool) ($employee->meal_allowance_enabled ?? true)) {
            return 0;
        }

        if (
            !is_null($employee->meal_allowance_rate) &&
            (int) $employee->meal_allowance_rate > 0
        ) {
            return (int) $employee->meal_allowance_rate;
        }

        $position = strtolower(trim((string) $employee->job_position));

        if (str_contains($position, 'teknisi')) {
            return 50000;
        }

        if (str_contains($position, 'detailer')) {
            return 50000;
        }

        if (str_contains($position, 'kenek')) {
            return 30000;
        }

        return 0;
    }

    private function isPresent(?string $status): bool
    {
        if (!$status) {
            return false;
        }

        return in_array(strtolower($status), $this->presentStatuses, true);
    }
}
