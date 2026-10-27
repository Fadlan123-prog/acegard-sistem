<?php

/**
 * Atur-password lewat email (undangan setelah approve + "lupa password").
 *
 * Sengaja DIBATASI ke role tertentu. Akun admin/owner/superadmin tidak bisa
 * di-reset lewat email: kalau kotak masuk mereka diambil alih, seluruh sistem
 * ikut jatuh. Password mereka diganti manual lewat menu Users oleh superadmin.
 * Perluas dengan PASSWORD_SETUP_ROLES=sales,marketing hanya setelah dipikirkan.
 */
return [
    'self_service_roles' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('PASSWORD_SETUP_ROLES', 'sales'))
    ))),

    // Panjang minimal password (huruf besar + kecil + angka tetap wajib).
    'min_length' => (int) env('PASSWORD_MIN_LENGTH', 10),
];
