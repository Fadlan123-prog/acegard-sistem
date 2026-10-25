<?php

namespace App\Services;

use App\Models\Employee;

class BpjsCalculatorService
{
    /**
     * Calculate all BPJS components for a given employee and salary basis.
     *
     * Returns a flat array with both employee deductions and employer contributions.
     * All values are in IDR, rounded to the nearest integer.
     */
    public function calculate(Employee $employee, int $basisSalary): array
    {
        $cfg = config('hr.bpjs');

        // ── Ketenagakerjaan ─────────────────────────────────────────────────
        $jhtEmployee  = 0;
        $jhtEmployer  = 0;
        $jpEmployee   = 0;
        $jpEmployer   = 0;
        $jkkEmployer  = 0;
        $jkmEmployer  = 0;

        if ($employee->bpjs_ketenagakerjaan_enrolled) {
            $jhtEmployee = (int) round($basisSalary * $cfg['jht_employee']);
            $jhtEmployer = (int) round($basisSalary * $cfg['jht_employer']);
            $jkmEmployer = (int) round($basisSalary * $cfg['jkm_employer']);

            $jkkRate     = $cfg['jkk_rates'][$employee->jkk_risk_group] ?? $cfg['jkk_rates'][1];
            $jkkEmployer = (int) round($basisSalary * $jkkRate);

            // JP uses a capped basis
            $jpBasis    = min($basisSalary, (int) $cfg['jp_wage_cap']);
            $jpEmployee = (int) round($jpBasis * $cfg['jp_employee']);
            $jpEmployer = (int) round($jpBasis * $cfg['jp_employer']);
        }

        // ── Kesehatan ───────────────────────────────────────────────────────
        $kesEmployee = 0;
        $kesEmployer = 0;

        if ($employee->bpjs_kesehatan_enrolled) {
            $kesBasis    = min($basisSalary, (int) $cfg['kes_wage_cap']);
            $kesEmployee = (int) round($kesBasis * $cfg['kes_employee']);
            $kesEmployer = (int) round($kesBasis * $cfg['kes_employer']);
        }

        $totalEmployee = $jhtEmployee + $jpEmployee + $kesEmployee;
        $totalEmployer = $jhtEmployer + $jpEmployer + $kesEmployer + $jkkEmployer + $jkmEmployer;

        return [
            'bpjs_jht_employee'  => $jhtEmployee,
            'bpjs_jp_employee'   => $jpEmployee,
            'bpjs_kes_employee'  => $kesEmployee,
            'bpjs_total_employee'=> $totalEmployee,

            'bpjs_jkk_employer'  => $jkkEmployer,
            'bpjs_jkm_employer'  => $jkmEmployer,
            'bpjs_jht_employer'  => $jhtEmployer,
            'bpjs_jp_employer'   => $jpEmployer,
            'bpjs_kes_employer'  => $kesEmployer,
            'bpjs_total_employer'=> $totalEmployer,
        ];
    }
}
