<?php

namespace App\Http\Controllers;

use App\Exports\PayrollMonthlyExport;
use App\Jobs\GenerateExportJob;
use App\Models\ExportRequest;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\SalaryAdjustment;
use App\Domain\HR\OffQuotaPolicy;
use App\Services\BranchContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function daily(Request $request)
{
    $user = auth()->user();

    $date = $request->input('date', now()->format('Y-m-d'));

    $branches = $user->hasRole('superadmin')
        ? Branch::orderBy('id')->get()
        : $user->branches()->orderBy('id')->get();

    /*
    |--------------------------------------------------------------------------
    | Ambil branch aktif
    |--------------------------------------------------------------------------
    | Prioritas:
    | 1. branch_id dari request, kalau user memilih dari dropdown
    | 2. session active_branch_id
    | 3. default Acegard Pusat / cabang pertama
    */
    $branchId = $request->filled('branch_id')
        ? (int) $request->branch_id
        : ($this->branch->idOrNull() ?? 0);

    if (!$branchId) {
        if ($user->hasRole('superadmin')) {
            $defaultBranch = Branch::where('name', 'like', '%pusat%')->first()
                ?? Branch::orderBy('id')->first();
        } else {
            $defaultBranch = $branches->first();
        }

        if ($defaultBranch) {
            $branchId = (int) $defaultBranch->id;
            $this->branch->switchTo($branchId);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validasi akses cabang
    |--------------------------------------------------------------------------
    */
    if ($user->hasRole('superadmin')) {
        if (!Branch::where('id', $branchId)->exists()) {
            $defaultBranch = Branch::where('name', 'like', '%pusat%')->first()
                ?? Branch::orderBy('id')->first();

            $branchId = $defaultBranch ? (int) $defaultBranch->id : 0;
            if ($branchId) {
                $this->branch->switchTo($branchId);
            }
        }
    } else {
        $allowedBranchIds = $user->branches->pluck('id')->map(fn ($id) => (int) $id);

        if (!$allowedBranchIds->contains((int) $branchId)) {
            $branchId = (int) $branches->first()->id;
            $this->branch->switchTo($branchId);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Jika user memilih cabang dari dropdown, update session juga
    |--------------------------------------------------------------------------
    */
    if ($request->filled('branch_id')) {
        $this->branch->switchTo($branchId);
    }

    $employeeIdsWithRecords = Attendance::where('branch_id', $branchId)
        ->whereDate('attendance_date', $date)
        ->pluck('employee_id');

    $employees = Employee::query()
        ->where('branch_id', $branchId)
        ->where(function ($q) use ($employeeIdsWithRecords) {
            $q->where('is_active', true)
              ->orWhereIn('id', $employeeIdsWithRecords);
        })
        ->orderBy('name')
        ->get();

    $attendanceMap = Attendance::where('branch_id', $branchId)
        ->whereDate('attendance_date', $date)
        ->get()
        ->keyBy('employee_id');

    return view('attendances.daily', compact(
        'date',
        'branches',
        'branchId',
        'employees',
        'attendanceMap'
    ));
}

    public function saveDaily(Request $request)
    {
        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'attendance_date' => ['required', 'date'],
            'attendances' => ['required', 'array'],
            'attendances.*.employee_id' => ['required', 'exists:employees,id'],
            'attendances.*.status' => ['required', 'in:present,off,sick,permission,absent'],
            'attendances.*.notes' => ['nullable', 'string'],
        ]);

        $user = auth()->user();

        if (!$user->hasRole('superadmin')) {
            $allowedBranchIds = $user->branches->pluck('id')->map(fn ($id) => (int) $id);

            if (!$allowedBranchIds->contains((int) $data['branch_id'])) {
                abort(403, 'Tidak boleh input absen cabang lain.');
            }
        }

        DB::beginTransaction();

        try {
            foreach ($data['attendances'] as $row) {
                $employee = Employee::where('id', $row['employee_id'])
                    ->where('branch_id', $data['branch_id'])
                    ->firstOrFail();

                $attendance = Attendance::where('employee_id', $employee->id)
                    ->whereDate('attendance_date', $data['attendance_date'])
                    ->first();

                $oldStatus = $attendance?->status;
                $newStatus = $row['status'];

                if ($attendance) {
                    $attendance->update([
                        'branch_id' => (int) $data['branch_id'],
                        'created_by' => auth()->id(),
                        'status' => $newStatus,
                        'daily_salary' => 0,
                        'notes' => $row['notes'] ?? null,
                    ]);
                } else {
                    $attendance = Attendance::create([
                        'branch_id' => (int) $data['branch_id'],
                        'employee_id' => $employee->id,
                        'created_by' => auth()->id(),
                        'attendance_date' => $data['attendance_date'],
                        'status' => $newStatus,
                        'daily_salary' => 0,
                        'notes' => $row['notes'] ?? null,
                    ]);
                }

                if ($oldStatus !== $newStatus) {
                    \App\Models\AttendanceLog::create([
                        'attendance_id' => $attendance->id,
                        'employee_id' => $employee->id,
                        'branch_id' => (int) $data['branch_id'],
                        'attendance_date' => $data['attendance_date'],
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'reason' => $row['notes'] ?? 'Koreksi absensi',
                        'changed_by' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('attendances.daily', [
                    'date' => $data['attendance_date'],
                    'branch_id' => $data['branch_id'],
                ])
                ->with('success', 'Absensi harian berhasil disimpan.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan absensi: ' . $e->getMessage());
        }
    }

    public function monthlyReport(Request $request)
    {
        $data = $this->buildMonthlyReportData($request);

        return view('attendances.monthly-report', $data);
    }

    public function saveAdjustments(Request $request)
    {
        $data = $request->validate([
            'month'           => ['required', 'string', 'size:7'],
            'adjustments'     => ['required', 'array'],
            'adjustments.*.employee_id'     => ['required', 'exists:employees,id'],
            'adjustments.*.uang_motor'      => ['nullable', 'numeric', 'min:0'],
            'adjustments.*.uang_makan'      => ['nullable', 'numeric', 'min:0'],
            'adjustments.*.potongan_rokok'  => ['nullable', 'numeric', 'min:0'],
            'adjustments.*.kasbon'          => ['nullable', 'numeric', 'min:0'],
            'adjustments.*.potongan_absen'  => ['nullable', 'numeric', 'min:0'],
        ]);

        foreach ($data['adjustments'] as $row) {
            SalaryAdjustment::updateOrCreate(
                ['employee_id' => $row['employee_id'], 'month' => $data['month']],
                [
                    'uang_motor'     => (int) ($row['uang_motor']     ?? 0),
                    'uang_makan'     => (int) ($row['uang_makan']     ?? 0),
                    'potongan_rokok' => (int) ($row['potongan_rokok'] ?? 0),
                    'kasbon'         => (int) ($row['kasbon']         ?? 0),
                    'potongan_absen' => (int) ($row['potongan_absen'] ?? 0),
                    'created_by'     => auth()->id(),
                ]
            );
        }

        return redirect()
            ->route('attendances.monthlyReport', ['month' => $data['month'], 'branch_id' => $request->branch_id])
            ->with('success', 'Tambahan & potongan gaji berhasil disimpan.');
    }

    public function exportPayroll(Request $request)
    {
        $data     = $this->buildMonthlyReportData($request);
        $format   = $request->input('format', 'excel');
        $stem     = 'laporan-gaji-' . \Str::slug($data['branchName']) . '-' . Carbon::parse($data['month'] . '-01')->format('Y-m');
        $type     = $format === 'pdf' ? 'attendance_pdf' : 'attendance_excel';
        $fileName = $stem . ($format === 'pdf' ? '.pdf' : '.xlsx');

        $exportRequest = ExportRequest::create([
            'type'      => $type,
            'params'    => ['month' => $data['month'], 'branch_id' => $data['branchId']],
            'file_name' => $fileName,
            'status'    => 'pending',
            'user_id'   => auth()->id(),
            'branch_id' => $data['branchId'] ?? null,
        ]);

        GenerateExportJob::dispatch($exportRequest->id);

        return redirect()->route('export-status.show', $exportRequest->id);
    }

    private function buildMonthlyReportData(Request $request): array
    {
        $user      = auth()->user();
        $month     = $request->input('month', now()->format('Y-m'));
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate   = Carbon::parse($month . '-01')->endOfMonth();
        $daysInMonth = $startDate->daysInMonth; // total hari di bulan ini (pengganti workingDays manual)

        $branches = $user->hasRole('superadmin')
            ? Branch::orderBy('id')->get()
            : $user->branches()->orderBy('id')->get();

        $branchId = $request->filled('branch_id')
            ? (int) $request->branch_id
            : ($this->branch->idOrNull() ?? 0);

        if ($branchId && !$user->hasRole('superadmin')) {
            $allowedBranchIds = $user->branches->pluck('id')->map(fn ($id) => (int) $id);
            if (!$allowedBranchIds->contains($branchId)) {
                $branchId = 0;
            }
        }

        if (!$branchId) {
            $default  = $user->hasRole('superadmin')
                ? Branch::where('name', 'like', '%pusat%')->first() ?? Branch::orderBy('id')->first()
                : $branches->first();
            $branchId = (int) ($default?->id ?? 0);
            if ($branchId) {
                $this->branch->switchTo($branchId);
            }
        }

        if ($request->filled('branch_id') && $branchId) {
            $this->branch->switchTo($branchId);
        }

        $branchName = Branch::find($branchId)?->name ?? 'Semua';

        $employeeIdsWithRecords = Attendance::where('branch_id', $branchId)
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('employee_id');

        $employees = Employee::where('branch_id', $branchId)
            ->where(function ($q) use ($employeeIdsWithRecords) {
                $q->where('is_active', true)
                  ->orWhereIn('id', $employeeIdsWithRecords);
            })
            ->orderBy('name')
            ->get();

        $attendances = Attendance::where('branch_id', $branchId)
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()->groupBy('employee_id');

        // Load adjustments bulan ini per karyawan
        $adjustmentsMap = SalaryAdjustment::whereIn('employee_id', $employees->pluck('id'))
            ->where('month', $month)
            ->get()
            ->keyBy('employee_id');

        $report = $employees->map(function ($employee) use ($attendances, $daysInMonth, $adjustmentsMap) {
            $rows        = $attendances->get($employee->id, collect());
            $present     = $rows->where('status', 'present')->count();
            $off         = $rows->where('status', 'off')->count();
            $sick        = $rows->where('status', 'sick')->count();
            $permission  = $rows->where('status', 'permission')->count();
            $absent      = $rows->where('status', 'absent')->count();

            $offQuota  = $this->getMonthlyOffQuota($employee->job_position);
            $paidOff   = min($off, $offQuota);
            $excessOff = max(0, $off - $offQuota);
            $unusedOff = max(0, $offQuota - $off);

            $monthlySalary = (int) ($employee->monthly_salary ?? 0);
            $workDays      = max(1, $daysInMonth - $offQuota); // hari kerja efektif = total hari bulan - jatah libur
            $dailyProrate  = $monthlySalary / $workDays;

            // Gaji = Rate × (Total Masuk + Sakit) — sakit tidak memotong gaji
            $baseGaji = $dailyProrate * ($present + $sick);

            // Adjustment: tambahan & potongan manual dari admin
            $adj              = $adjustmentsMap->get($employee->id);
            $uangMotor        = (int) ($adj?->uang_motor     ?? 0);
            $uangMakan        = (int) ($adj?->uang_makan     ?? 0);
            $potonganRokok    = (int) ($adj?->potongan_rokok ?? 0);
            $kasbon           = (int) ($adj?->kasbon         ?? 0);
            $potonganAbsen    = (int) ($adj?->potongan_absen ?? 0);
            $totalTambahan    = $uangMotor + $uangMakan;
            $totalPotongan    = $potonganRokok + $kasbon + $potonganAbsen;

            $salaryRaw = max(0, $baseGaji + $totalTambahan - $totalPotongan);

            // Untuk tampilan
            $deductionDays   = $absent + $excessOff;
            $deductionAmount = $deductionDays * $dailyProrate;
            $bonusAmount     = $unusedOff * $dailyProrate;

            return [
                'employee'         => $employee,
                'present'          => $present,
                'off'              => $off,
                'sick'             => $sick,
                'permission'       => $permission,
                'absent'           => $absent,
                'off_quota'        => $offQuota,
                'paid_off'         => $paidOff,
                'excess_off'       => $excessOff,
                'unused_off'       => $unusedOff,
                'work_days'        => $workDays,
                'deduction_days'   => $deductionDays,
                'deduction_amount' => $deductionAmount,
                'bonus_amount'     => $bonusAmount,
                'uang_motor'       => $uangMotor,
                'uang_makan'       => $uangMakan,
                'potongan_rokok'   => $potonganRokok,
                'kasbon'           => $kasbon,
                'potongan_absen'   => $potonganAbsen,
                'total_tambahan'   => $totalTambahan,
                'total_potongan'   => $totalPotongan,
                'monthly_salary'   => $monthlySalary,
                'daily_prorate'    => $dailyProrate,
                'base_gaji'        => (int) round($baseGaji),
                'salary_raw'       => (int) round($salaryRaw),
                'salary'           => (int) (round($salaryRaw / 1000) * 1000),
            ];
        });

        return compact('month', 'startDate', 'endDate', 'daysInMonth', 'branches', 'branchId', 'branchName', 'report');
    }

    public function attendanceReport(Request $request)
    {
        $user   = auth()->user();
        $month  = $request->input('month', now()->format('Y-m'));

        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate   = Carbon::parse($month . '-01')->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $branches = $user->hasRole('superadmin')
            ? Branch::orderBy('id')->get()
            : $user->branches()->orderBy('id')->get();

        $branchId = $request->filled('branch_id')
            ? (int) $request->branch_id
            : ($this->branch->idOrNull() ?? 0);

        if ($branchId && !$user->hasRole('superadmin')) {
            $allowedBranchIds = $user->branches->pluck('id')->map(fn ($id) => (int) $id);
            if (!$allowedBranchIds->contains($branchId)) {
                $branchId = 0;
            }
        }

        if (!$branchId) {
            $defaultBranch = $user->hasRole('superadmin')
                ? Branch::where('name', 'like', '%pusat%')->first() ?? Branch::orderBy('id')->first()
                : $branches->first();

            if ($defaultBranch) {
                $branchId = (int) $defaultBranch->id;
                $this->branch->switchTo($branchId);
            }
        }

        if ($request->filled('branch_id') && $branchId) {
            $this->branch->switchTo($branchId);
        }

        $employeeIdsWithRecords = Attendance::where('branch_id', $branchId)
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('employee_id');

        $employees = Employee::where('branch_id', $branchId)
            ->where(function ($q) use ($employeeIdsWithRecords) {
                $q->where('is_active', true)
                  ->orWhereIn('id', $employeeIdsWithRecords);
            })
            ->orderBy('name')
            ->get();

        // [ employee_id => [ 'YYYY-MM-DD' => status ] ]
        $attendanceMap = Attendance::where('branch_id', $branchId)
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->groupBy('employee_id')
            ->map(fn ($rows) => $rows->keyBy(fn ($a) => Carbon::parse($a->attendance_date)->format('Y-m-d')));

        $statusLabel = [
            'present'    => 'M',
            'off'        => 'L',
            'sick'       => 'S',
            'permission' => 'I',
            'absent'     => 'A',
        ];

        $statusColor = [
            'present'    => 'success',
            'off'        => 'secondary',
            'sick'       => 'info',
            'permission' => 'warning',
            'absent'     => 'danger',
        ];

        return view('attendances.attendance-report', compact(
            'month', 'startDate', 'endDate', 'daysInMonth',
            'branches', 'branchId', 'employees',
            'attendanceMap', 'statusLabel', 'statusColor'
        ));
    }

    protected function getMonthlyOffQuota(?string $jobPosition): int
    {
        return OffQuotaPolicy::forPosition((string) $jobPosition);
    }
}
