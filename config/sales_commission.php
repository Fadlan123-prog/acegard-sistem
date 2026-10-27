<?php

/**
 * Konfigurasi komisi fitur Referral & Hierarki Sales.
 *
 * Sengaja dipisah dari config/commission.php (yang dipakai pipeline kaca film
 * mobil & PPF yang sudah ada) karena ini sistem komisi yang berbeda total —
 * tidak ada hubungannya dengan tukang/kenek/marketing_id di Customer/Invoice.
 */
return [
    // Komisi sales yang closing — persentase dari total_price (setelah diskon).
    'closing_rate' => 0.035, // 3,5%

    // Komisi referral berdasarkan stage (1/2/3 langkah ke atas dari sponsor
    // chain si closer). Hanya dibayar kalau penerima di posisi itu ber-role
    // 'sales' — kalau 'marketing' atau tidak ada, stage itu dilewati (tidak
    // dibayar, tidak dialihkan).
    'stage' => [
        1 => 300000,
        2 => 100000,
        3 => 50000,
    ],

    // Komisi marketing referral — flat, dibayar ke employee ber-role
    // 'marketing' PERTAMA yang ditemukan saat jalan ke atas sponsor chain
    // dari sales yang closing, TANPA batas kedalaman. Marketing tidak pernah
    // menerima komisi stage di atas — ini satu-satunya komisi yang bisa
    // mereka terima dari fitur ini.
    'marketing_referral' => 70000,
];
