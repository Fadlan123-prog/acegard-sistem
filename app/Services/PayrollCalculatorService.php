<?php

namespace App\Services;

use App\Domain\HR\OffQuotaPolicy;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\SalaryAdjustment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollCalculatorService
{
    public function __construct(
        private readonly OvertimeService $overtime,
        private readonly BpjsCalculatorService $bpjs,
    ) {}

    public function generate(
        int     $branchId,
        string  $month,
        int     $workingDays,
        ?int    $generatedBy,
        ?string $notes,
        bool    $regenerate
    ): Payroll {
        $existingPayroll = Payroll::where('branch_id', $branchId)->where('month', $month)->first();

        if ($existingPayroll && !$regenerate) {
            return $existingPayroll;
        }

        if ($existingPayroll && $existingPayroll->status !== 'draft') {
            throw new \RuntimeException('Payroll yang sudah approved/paid tidak dapat dibuat ulang.');
        }

        return DB::transaction(function () use ($branchId, $month, $workingDays, $generatedBy, $notes) {
            $payroll = Payroll::updateOrCreate(
                ['branch_id' => $branchId, 'month' => $month],
                ['working_days' => $workingDays, 'status' => 'draft', 'generated_by' => $generatedBy, 'notes' => $notes]
            );

            PayrollItem::where('payroll_id', $payroll->id)->delete();

            $startDate = Carbon::parse($month . '-01')->startOfMonth();
            $endDate   = Carbon::parse($month . '-01')->endOfMonth();

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

            foreach ($employees as $employee) {
                PayrollItem::create($this->buildPayrollItemData(
                    payroll:       $payroll,
                    employee:      $employee,
                    attendanceRows: $attendances->get($employee->id, collect()),
                    workingDays:   $workingDays,
                    adjustment:    $adjustmentsMap->get($employee->id),
                ));
            }

            return $payroll;
        });
    }

    public function refresh(Payroll $payroll): void
    {
        if ($payroll->status !== 'draft') {
            throw new \RuntimeException('Hanya payroll draft yang bisa direfresh.');
        }

        DB::transaction(function () use ($payroll) {
            $startDate = Carbon::parse($payroll->month . '-01')->startOfMonth();
            $endDate   = Carbon::parse($payroll->month . '-01')->endOfMonth();

            $employees = Employee::where('branch_id', $payroll->branch_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            $attendances = Attendance::where('branch_id', $payroll->branch_id)
                ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get()
                ->groupBy('employee_id');

            $adjustmentsMap = SalaryAdjustment::whereIn('employee_id', $employees->pluck('id'))
                ->where('month', $payroll->month)
                ->get()
                ->keyBy('employee_id');

            foreach ($employees as $employee) {
                $itemData = $this->buildPayrollItemData(
                    payroll:       $payroll,
                    employee:      $employee,
                    attendanceRows: $attendances->get($employee->id, collect()),
                    workingDays:   (int) $payroll->working_days,
                    adjustment:    $adjustmentsMap->get($employee->id),
                );

                $existing = PayrollItem::where('payroll_id', $payroll->id)
                    ->where('employee_id', $employee->id)
                    ->first();

                if ($existing) {
                    if ($existing->notes && !$itemData['notes']) {
                        $itemData['notes'] = $existing->notes;
                    }
                    $existing->update($itemData);
                } else {
                    PayrollItem::create($itemData);
                }
            }
        });
    }

    public function buildPayrollItemData(
        Payroll          $payroll,
        Employee         $employee,
        $attendanceRows,
        int              $workingDays,
        ?SalaryAdjustment $adjustment = null
    ): array {
        $present    = $attendanceRows->where('status', 'present')->count();
        $off        = $attendanceRows->where('status', 'off')->count();
        $sick       = $attendanceRows->where('status', 'sick')->count();
        $permission = $attendanceRows->where('status', 'permission')->count();
        $absent     = $attendanceRows->where('status', 'absent')->count();

        $offQuota  = OffQuotaPolicy::forPosition((string) $employee->job_position);
        $paidOff   = min($off, $offQuota);
        $excessOff = max(0, $off - $offQuota);

        $monthlySalary  = (int) ($employee->monthly_salary ?? 0);
        $startDate      = Carbon::parse($payroll->month . '-01');
        $daysInMonth    = $startDate->daysInMonth;
        $effectiveDays  = max(1, $daysInMonth - $offQuota);
        $dailyProrate   = $monthlySalary / $effectiveDays;

        $paidDays    = $present + $paidOff + $sick + $permission;
        $prorateSalary = (int) round($dailyProrate * ($present + $sick));

        $transportAllowance  = (int) ($adjustment?->uang_motor      ?? 0);
        $mealAllowance       = (int) ($adjustment?->uang_makan      ?? 0);
        $mainDeduction       = (int) ($adjustment?->potongan_rokok  ?? 0);
        $cashAdvance         = (int) ($adjustment?->kasbon          ?? 0);
        $attendanceDeduction = (int) ($adjustment?->potongan_absen  ?? 0);

        $totalOvertimeMinutes = (int) $attendanceRows->sum('overtime_minutes');
        $totalLateMinutes     = (int) $attendanceRows->sum('late_minutes');
        $overtimePay          = $this->overtime->overtimePay($dailyProrate, $totalOvertimeMinutes);
        $lateDeduction        = $this->overtime->lateDeduction($dailyProrate, $totalLateMinutes);

        $grossBeforeBpjs = $prorateSalary + $transportAllowance + $mealAllowance + $overtimePay
            - $mainDeduction - $cashAdvance - $attendanceDeduction - $lateDeduction;

        $bpjs              = $this->bpjs->calculate($employee, $prorateSalary);
        $bpjsEmployeeTotal = $bpjs['bpjs_total_employee'];

        $totalSalary = max(0, $grossBeforeBpjs - $bpjsEmployeeTotal);

        return [
            'payroll_id'           => $payroll->id,
            'employee_id'          => $employee->id,
            'employee_name'        => $employee->name,
            'job_position'         => $employee->job_position,
            'monthly_salary'       => $monthlySalary,
            'daily_prorate'        => $dailyProrate,
            'present_days'         => $present,
            'off_days'             => $off,
            'off_quota'            => $offQuota,
            'paid_off_days'        => $paidOff,
            'excess_off_days'      => $excessOff,
            'sick_days'            => $sick,
            'permission_days'      => $permission,
            'absent_days'          => $absent,
            'paid_days'            => $present + $sick,
            'prorate_salary'       => $prorateSalary,
            'transport_allowance'  => $transportAllowance,
            'meal_allowance'       => $mealAllowance,
            'main_deduction'       => $mainDeduction,
            'cash_advance'         => $cashAdvance,
            'attendance_deduction' => $attendanceDeduction,
            'other_deduction'      => 0,
            'overtime_minutes'     => $totalOvertimeMinutes,
            'overtime_pay'         => $overtimePay,
            'late_deduction'       => $lateDeduction,

            // BPJS — spread the array returned by BpjsCalculatorService
            ...$bpjs,

            'total_salary'         => $totalSalary,
            'rounded_salary'       => $this->roundToThousand($totalSalary),
            'notes'                => null,
        ];
    }

    public function roundToThousand(int|float $amount): int
    {
        return (int) round($amount / 1000) * 1000;
    }
}
