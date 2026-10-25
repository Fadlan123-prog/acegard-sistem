<?php

namespace App\Jobs;

use App\Domain\HR\OffQuotaPolicy;
use App\Exports\CommissionExport;
use App\Exports\PayrollMonthlyExport;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\ExportRequest;
use App\Models\SalaryAdjustment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class GenerateExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 300;

    public function __construct(private int $exportRequestId) {}

    public function handle(): void
    {
        $req = ExportRequest::find($this->exportRequestId);
        if (!$req) return;

        $req->markProcessing();

        try {
            $path = match ($req->type) {
                'commission_excel'  => $this->generateCommissionExcel($req),
                'attendance_excel'  => $this->generateAttendanceExcel($req),
                'attendance_pdf'    => $this->generateAttendancePdf($req),
                default             => throw new \RuntimeException("Unknown export type: {$req->type}"),
            };

            $req->markDone(['file_path' => $path]);

        } catch (\Throwable $e) {
            Log::error('[GenerateExportJob] failed', [
                'export_request_id' => $this->exportRequestId,
                'type'              => $req->type,
                'error'             => $e->getMessage(),
            ]);
            $req->markFailed($e->getMessage());
        }
    }

    private function generateCommissionExcel(ExportRequest $req): string
    {
        $filters  = $req->params['filters'] ?? [];
        $fileName = $req->file_name;
        $path     = 'exports/' . $fileName;

        Excel::store(new CommissionExport($filters), $path, 'local');

        return $path;
    }

    private function generateAttendanceExcel(ExportRequest $req): string
    {
        $p      = $req->params;
        $data   = $this->buildAttendanceData($p['month'], (int) $p['branch_id']);
        $path   = 'exports/' . $req->file_name;

        Excel::store(
            new PayrollMonthlyExport($data['report'], $data['month'], $data['daysInMonth'], $data['branchName']),
            $path,
            'local'
        );

        return $path;
    }

    private function generateAttendancePdf(ExportRequest $req): string
    {
        $p    = $req->params;
        $data = $this->buildAttendanceData($p['month'], (int) $p['branch_id']);
        $path = 'exports/' . $req->file_name;

        $pdf = Pdf::loadView('attendances.monthly-report-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isRemoteEnabled'     => false,
                'isHtml5ParserEnabled'=> true,
                'defaultFont'         => 'Poppins',
                'fontDir'             => storage_path('fonts'),
                'fontCache'           => storage_path('fonts'),
                'chroot'              => storage_path('fonts'),
            ]);

        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    private function buildAttendanceData(string $month, int $branchId): array
    {
        $startDate   = Carbon::parse($month . '-01')->startOfMonth();
        $endDate     = Carbon::parse($month . '-01')->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;
        $branchName  = Branch::find($branchId)?->name ?? 'Semua';

        $employees = Employee::where('branch_id', $branchId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $attendances = Attendance::where('branch_id', $branchId)
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->groupBy('employee_id');

        $adjustmentsMap = SalaryAdjustment::whereIn('employee_id', $employees->pluck('id'))
            ->where('month', $month)
            ->get()
            ->keyBy('employee_id');

        $report = $employees->map(function ($employee) use ($attendances, $daysInMonth, $adjustmentsMap) {
            $rows       = $attendances->get($employee->id, collect());
            $present    = $rows->where('status', 'present')->count();
            $off        = $rows->where('status', 'off')->count();
            $sick       = $rows->where('status', 'sick')->count();
            $permission = $rows->where('status', 'permission')->count();
            $absent     = $rows->where('status', 'absent')->count();

            $offQuota  = OffQuotaPolicy::forPosition((string) $employee->job_position);
            $paidOff   = min($off, $offQuota);
            $excessOff = max(0, $off - $offQuota);
            $unusedOff = max(0, $offQuota - $off);

            $monthlySalary = (int) ($employee->monthly_salary ?? 0);
            $workDays      = max(1, $daysInMonth - $offQuota);
            $dailyProrate  = $monthlySalary / $workDays;
            $baseGaji      = $dailyProrate * ($present + $sick);

            $adj           = $adjustmentsMap->get($employee->id);
            $uangMotor     = (int) ($adj?->uang_motor     ?? 0);
            $uangMakan     = (int) ($adj?->uang_makan     ?? 0);
            $potonganRokok = (int) ($adj?->potongan_rokok ?? 0);
            $kasbon        = (int) ($adj?->kasbon         ?? 0);
            $potonganAbsen = (int) ($adj?->potongan_absen ?? 0);
            $totalTambahan = $uangMotor + $uangMakan;
            $totalPotongan = $potonganRokok + $kasbon + $potonganAbsen;
            $salaryRaw     = max(0, $baseGaji + $totalTambahan - $totalPotongan);

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
                'deduction_days'   => $absent + $excessOff,
                'deduction_amount' => ($absent + $excessOff) * $dailyProrate,
                'bonus_amount'     => $unusedOff * $dailyProrate,
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

        return compact('month', 'startDate', 'endDate', 'daysInMonth', 'branchName', 'report');
    }
}
