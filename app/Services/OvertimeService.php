<?php

namespace App\Services;

use App\Models\Attendance;
use Carbon\Carbon;

class OvertimeService
{
    /**
     * Recalculate late_minutes and overtime_minutes for an attendance record
     * that has a shift assigned and both check_in_at / check_out_at set.
     *
     * Persists the calculated values to the database.
     */
    public function calculate(Attendance $attendance): void
    {
        if (!$attendance->shift_id || !$attendance->check_in_at) {
            return;
        }

        $shift = $attendance->shift ?? $attendance->load('shift')->shift;
        $date  = $attendance->attendance_date;

        $shiftStart = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->start_time);
        $shiftEnd   = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->end_time);

        // Handle overnight shifts (e.g. 22:00 → 06:00)
        if ($shiftEnd->lte($shiftStart)) {
            $shiftEnd->addDay();
        }

        // Late minutes: how many minutes after shift start the employee checked in
        $lateMinutes = max(0, (int) $shiftStart->diffInMinutes($attendance->check_in_at, false));

        // Overtime minutes: how many minutes past (shift end + grace period) the employee worked
        $overtimeMinutes = 0;
        if ($attendance->check_out_at) {
            $overtimeThreshold = $shiftEnd->copy()->addMinutes((int) $shift->overtime_after_minutes);
            $overtimeMinutes   = max(0, (int) $overtimeThreshold->diffInMinutes($attendance->check_out_at, false));
        }

        $attendance->update([
            'late_minutes'     => $lateMinutes,
            'overtime_minutes' => $overtimeMinutes,
        ]);
    }

    /**
     * Calculate overtime pay for a given number of overtime minutes.
     *
     * Formula: (dailyProrate / shiftHours) × 1.5 × (overtimeMinutes / 60)
     * shiftHours defaults to 8 when no shift is provided.
     */
    public function overtimePay(float $dailyProrate, int $overtimeMinutes, int $shiftMinutes = 480): int
    {
        if ($overtimeMinutes <= 0 || $shiftMinutes <= 0) {
            return 0;
        }

        $hourlyRate    = $dailyProrate / ($shiftMinutes / 60);
        $overtimeHours = $overtimeMinutes / 60;
        $multiplier    = (float) config('hr.overtime_multiplier', 1.5);

        return (int) round($hourlyRate * $multiplier * $overtimeHours);
    }

    /**
     * Calculate late deduction for a given number of late minutes.
     *
     * Formula: (dailyProrate / shiftHours) × (lateMinutes / 60)
     * Only applied when late_minutes > configured grace period.
     */
    public function lateDeduction(float $dailyProrate, int $lateMinutes, int $shiftMinutes = 480): int
    {
        $graceMinutes = (int) config('hr.late_grace_minutes', 15);

        if ($lateMinutes <= $graceMinutes || $shiftMinutes <= 0) {
            return 0;
        }

        $deductibleMinutes = $lateMinutes - $graceMinutes;
        $hourlyRate        = $dailyProrate / ($shiftMinutes / 60);

        return (int) round($hourlyRate * ($deductibleMinutes / 60));
    }
}
