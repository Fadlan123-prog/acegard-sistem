<?php

namespace App\Domain\HR;

/**
 * Single canonical source for monthly paid-off-day quota rules.
 *
 * Fixes the silent divergence between two independent implementations:
 *
 *   PayrollController::getMonthlyOffQuota()    — used str_contains('teknisi')
 *   AttendanceController::getMonthlyOffQuota() — used in_array(['Teknisi', 'Kenek'])
 *
 * The in_array version did NOT match "Teknisi Senior", giving them 4 off-days
 * in attendance reports but 3 in payroll — a silent calculation discrepancy.
 *
 * This class uses str_contains (case-insensitive) to match all teknisi variants.
 *
 * Usage:
 *   $quota = OffQuotaPolicy::forPosition($employee->job_position);
 *   // "Teknisi"        → 3
 *   // "Teknisi Senior" → 3  (was incorrectly 4 in AttendanceController)
 *   // "Kenek"          → 3
 *   // "Marketing"      → 4
 *   // "Admin"          → 4
 */
final class OffQuotaPolicy
{
    /**
     * Job position fragments that qualify for the reduced (field-work) off quota.
     * Matched case-insensitively via str_contains.
     */
    private const FIELD_POSITIONS = ['teknisi', 'kenek', 'detailer'];

    /**
     * Off-day quotas by category.
     */
    private const QUOTA_FIELD  = 3; // teknisi, kenek
    private const QUOTA_OFFICE = 4; // all other positions

    public static function forPosition(string $jobPosition): int
    {
        $normalized = strtolower($jobPosition);

        foreach (self::FIELD_POSITIONS as $fragment) {
            if (str_contains($normalized, $fragment)) {
                return self::QUOTA_FIELD;
            }
        }

        return self::QUOTA_OFFICE;
    }

    /**
     * Returns true if the position qualifies for the field-work quota.
     */
    public static function isFieldPosition(string $jobPosition): bool
    {
        return self::forPosition($jobPosition) === self::QUOTA_FIELD;
    }
}
