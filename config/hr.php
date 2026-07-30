<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Overtime
    |--------------------------------------------------------------------------
    */
    'overtime_multiplier' => 1.5,
    'late_grace_minutes'  => 15,

    /*
    |--------------------------------------------------------------------------
    | BPJS Rates
    |
    | All rates are expressed as decimals (e.g. 0.02 = 2%).
    | Caps are in IDR per month (government-mandated wage ceilings for
    | contribution basis calculation).
    |--------------------------------------------------------------------------
    */
    'bpjs' => [

        // JHT (Jaminan Hari Tua / Old-Age Savings)
        'jht_employee' => 0.02,   // 2%  — deducted from employee
        'jht_employer' => 0.037,  // 3.7% — paid by employer

        // JP (Jaminan Pensiun / Pension)
        'jp_employee'  => 0.01,   // 1%  — deducted from employee
        'jp_employer'  => 0.02,   // 2%  — paid by employer
        'jp_wage_cap'  => 9_559_600, // IDR — monthly wage ceiling for JP basis

        // JKM (Jaminan Kematian / Death Benefit) — employer only
        'jkm_employer' => 0.003,  // 0.30%

        // JKK (Jaminan Kecelakaan Kerja / Work Accident) — employer only, by risk group
        'jkk_rates' => [
            1 => 0.0024, // 0.24% — low risk
            2 => 0.0054, // 0.54%
            3 => 0.0089, // 0.89%
            4 => 0.0127, // 1.27%
            5 => 0.0174, // 1.74% — high risk
        ],

        // Kesehatan (BPJS Health)
        'kes_employee'  => 0.01,  // 1%  — deducted from employee
        'kes_employer'  => 0.04,  // 4%  — paid by employer
        'kes_wage_cap'  => 12_000_000, // IDR — monthly wage ceiling for Kesehatan basis
    ],

];
