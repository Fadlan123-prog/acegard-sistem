<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default annual health claim allocation
    |--------------------------------------------------------------------------
    |
    | Plafon default (Rupiah) yang dipakai saat employee_health_balances baris
    | pertama seorang karyawan dibuat. Defaultnya sengaja 0, bukan angka
    | tebakan — set DEFAULT_HEALTH_ALLOCATION di .env ke nominal yang benar
    | sebelum fitur Saldo Biaya Kesehatan dipakai sungguhan.
    |
    */
    'default_health_allocation' => (int) env('DEFAULT_HEALTH_ALLOCATION', 0),
];
