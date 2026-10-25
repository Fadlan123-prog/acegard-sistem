<?php

namespace App\Domain\Commission;

use App\Models\Employee;

/**
 * Applies a per-employee commission setting (Employee::commission_mode) on top
 * of whatever the normal calculation would have produced — flat rates for car
 * jobs (CommissionCalculator), fixed percentages for PPF (CommissionService).
 *
 * Pure: no DB writes, no side effects, never lazy-loads. $employee must already
 * have commission_mode / commission_percentage available (plain attributes).
 */
final class EmployeeCommissionResolver
{
    /**
     * @param  Employee|null  $employee        Recipient of this commission row. Null (e.g. no kenek
     *                                          assigned) resolves to the default amount untouched.
     * @param  int            $defaultAmount   Amount the existing calculation would pay (rupiah).
     * @param  int            $baseAmount      Invoice price / total_price — basis for percentage mode.
     * @return array{amount:int, forced:bool}  forced=true means this is an explicit per-employee
     *                                          override (none/percentage) — the row must still be
     *                                          inserted even when amount resolves to 0, so the
     *                                          exclusion itself stays visible/auditable.
     */
    public function resolve(?Employee $employee, int $defaultAmount, int $baseAmount): array
    {
        $mode = $employee?->commission_mode ?? 'default';

        return match ($mode) {
            'none'       => ['amount' => 0, 'forced' => true],
            'percentage' => [
                'amount' => (int) round($baseAmount * (float) ($employee->commission_percentage ?? 0)),
                'forced' => true,
            ],
            default => ['amount' => $defaultAmount, 'forced' => false],
        };
    }
}
