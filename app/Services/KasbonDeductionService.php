<?php

namespace App\Services;

use App\Models\CommissionPaymentBatch;
use App\Models\Employee;
use App\Models\Kasbon;
use App\Models\KasbonDeduction;
use App\Models\SalaryAdjustment;
use Illuminate\Support\Facades\DB;

/**
 * Applies one installment of an employee's active Kasbon against a payroll
 * period or a commission withdrawal batch, capped at 50% of whatever is
 * actually being paid out in that period (not the flat monthly_salary used
 * for the up-front sanity check at request time — see KasbonController).
 *
 * Both methods are safe to call repeatedly for the same period/batch: the
 * unique indexes on kasbon_deductions (kasbon_id+source+period for payroll,
 * kasbon_id+commission_payment_batch_id for commission) make the insert a
 * no-op the second time, so a payroll "refresh" never double-deducts.
 */
class KasbonDeductionService
{
    public function applyForPayroll(Employee $employee, string $month, int $periodSalary): void
    {
        $kasbon = $this->activeKasbonFor($employee, ['payroll', 'both']);
        if (! $kasbon) {
            return;
        }

        if (KasbonDeduction::where('kasbon_id', $kasbon->id)->where('source', 'payroll')->where('period', $month)->exists()) {
            return;
        }

        $take = $this->cappedInstallment($kasbon, $periodSalary);
        if ($take <= 0) {
            return;
        }

        DB::transaction(function () use ($kasbon, $employee, $month, $take) {
            $adjustment = SalaryAdjustment::firstOrCreate(
                ['employee_id' => $employee->id, 'month' => $month],
                ['created_by' => $kasbon->requested_by]
            );
            $adjustment->increment('kasbon', $take);

            KasbonDeduction::create([
                'kasbon_id' => $kasbon->id,
                'source'    => 'payroll',
                'period'    => $month,
                'amount'    => $take,
            ]);

            $this->settle($kasbon, $take);
        });
    }

    public function applyForCommission(Employee $employee, CommissionPaymentBatch $batch): void
    {
        $kasbon = $this->activeKasbonFor($employee, ['commission', 'both']);
        if (! $kasbon) {
            return;
        }

        if (KasbonDeduction::where('kasbon_id', $kasbon->id)->where('commission_payment_batch_id', $batch->id)->exists()) {
            return;
        }

        $take = $this->cappedInstallment($kasbon, (int) $batch->total_amount);
        if ($take <= 0) {
            return;
        }

        DB::transaction(function () use ($kasbon, $batch, $take) {
            KasbonDeduction::create([
                'kasbon_id'                    => $kasbon->id,
                'source'                       => 'commission',
                'commission_payment_batch_id'  => $batch->id,
                'amount'                       => $take,
            ]);

            $this->settle($kasbon, $take);
        });
    }

    private function activeKasbonFor(Employee $employee, array $sources): ?Kasbon
    {
        return Kasbon::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->whereIn('deduction_source', $sources)
            ->where('remaining_amount', '>', 0)
            ->oldest()
            ->first();
    }

    private function cappedInstallment(Kasbon $kasbon, int $periodAmount): int
    {
        $cap = (int) floor(0.5 * $periodAmount);

        return max(0, min($kasbon->installment_amount, $kasbon->remaining_amount, $cap));
    }

    private function settle(Kasbon $kasbon, int $take): void
    {
        $kasbon->decrement('remaining_amount', $take);
        $kasbon->increment('installments_paid');

        if ($kasbon->fresh()->remaining_amount <= 0) {
            $kasbon->update(['status' => 'completed']);
        }
    }
}
