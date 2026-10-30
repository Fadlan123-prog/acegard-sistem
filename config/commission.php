<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Komisi Fixed Teknisi & Kenek
    |--------------------------------------------------------------------------
    |
    | Key format:
    |   {type}              → standard (beda jabatan, 70:30)
    |   {type}_50           → jabatan sama / pasangan sejenis (50:50)
    |   {type}_solo         → tanpa kenek, satu installer mengambil semua
    |   {type}_lebaran      → bonus lebaran (2× standard)
    |
    | Nilai adalah jumlah MASING-MASING orang, bukan pool total.
    | Sistem akan memilih key berdasarkan kondisi pasangan yang terdeteksi.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Bonus Panoramic
    |--------------------------------------------------------------------------
    | Ditambahkan di atas komisi base untuk semua tipe pemasangan.
    | Berlaku flat per orang (tidak mengikuti rasio split).
    */
    'panoramic_bonus' => ['teknisi' => 30000, 'kenek' => 15000],


    'fixed' => [

        // ── D / S / P ────────────────────────────────────────────────────────
        'dsp'       => ['teknisi' => 35000, 'kenek' => 15000],
        'dsp_50'    => ['teknisi' => 25000, 'kenek' => 25000],
        'dsp_solo'  => ['teknisi' => 50000, 'kenek' => 50000],

        // ── D / S / P + Panoramic = 2× D/S/P ────────────────────────────────
        // Hanya bila bagian yang dipasang persis kaca depan + panoramic
        // (CommissionCalculator::isWindshieldPlusPanoramicOnly). Salah satunya saja = 1× D/S/P.
        'dsp_panoramic'      => ['teknisi' => 70000,  'kenek' => 30000],   // 2 × dsp
        'dsp_panoramic_50'   => ['teknisi' => 50000,  'kenek' => 50000],   // 2 × dsp_50
        'dsp_panoramic_solo' => ['teknisi' => 100000, 'kenek' => 100000],  // 2 × dsp_solo

        // ── SKKB ─────────────────────────────────────────────────────────────
        'skkb'      => ['teknisi' => 49000, 'kenek' => 21000],
        'skkb_50'   => ['teknisi' => 35000, 'kenek' => 35000],
        'skkb_solo' => ['teknisi' => 70000, 'kenek' => 70000],

        // ── SKKB + Panoramic (base + 30k/15k) ───────────────────────────────
        'skkb_panoramic'      => ['teknisi' => 79000,  'kenek' => 36000],   // skkb + 30k/15k
        'skkb_panoramic_50'   => ['teknisi' => 57500,  'kenek' => 57500],   // (skkb_50 pool + 45k) ÷ 2
        'skkb_panoramic_solo' => ['teknisi' => 115000, 'kenek' => 115000],  // skkb_solo pool + 45k

        // ── Full Set ─────────────────────────────────────────────────────────
        'fullset'      => ['teknisi' => 70000,  'kenek' => 30000],
        'fullset_50'   => ['teknisi' => 50000,  'kenek' => 50000],
        'fullset_solo' => ['teknisi' => 100000, 'kenek' => 100000],

        // ── Full + Panoramic (base + 30k/15k) ───────────────────────────────
        'full_panoramic'      => ['teknisi' => 100000, 'kenek' => 45000],   // fullset + 30k/15k
        'full_panoramic_50'   => ['teknisi' => 72500,  'kenek' => 72500],   // (fullset_50 pool + 45k) ÷ 2
        'full_panoramic_solo' => ['teknisi' => 145000, 'kenek' => 145000],  // fullset_solo pool + 45k

        // ── Full Set Ekstra ──────────────────────────────────────────────────
        'fullset_ekstra'      => ['teknisi' => 105000, 'kenek' => 45000],
        'fullset_ekstra_50'   => ['teknisi' => 75000,  'kenek' => 75000],
        'fullset_ekstra_solo' => ['teknisi' => 150000, 'kenek' => 150000],

        // ── Ekstra + Panoramic (base + 30k/15k) ─────────────────────────────
        'ekstra_panoramic'      => ['teknisi' => 135000, 'kenek' => 60000],   // fullset_ekstra + 30k/15k
        'ekstra_panoramic_50'   => ['teknisi' => 97500,  'kenek' => 97500],   // (fullset_ekstra_50 pool + 45k) ÷ 2
        'ekstra_panoramic_solo' => ['teknisi' => 195000, 'kenek' => 195000],  // fullset_ekstra_solo pool + 45k

        // ── Full Set Extra Plus ──────────────────────────────────────────────
        'fullset_ekstra_plus'      => ['teknisi' => 140000, 'kenek' => 60000],
        'fullset_ekstra_plus_50'   => ['teknisi' => 100000, 'kenek' => 100000],
        'fullset_ekstra_plus_solo' => ['teknisi' => 200000, 'kenek' => 200000],

        // ── Extra Plus + Panoramic (base + 30k/15k) ─────────────────────────
        'fullset_ekstra_plus_panoramic'      => ['teknisi' => 170000, 'kenek' => 75000],   // ekstra_plus + 30k/15k
        'fullset_ekstra_plus_panoramic_50'   => ['teknisi' => 122500, 'kenek' => 122500],  // (ekstra_plus_50 pool + 45k) ÷ 2
        'fullset_ekstra_plus_panoramic_solo' => ['teknisi' => 245000, 'kenek' => 245000],  // ekstra_plus_solo pool + 45k

        // ── Lebaran (2× rate standard) ───────────────────────────────────────
        'dsp_lebaran'                        => ['teknisi' => 70000,  'kenek' => 30000],
        'dsp_panoramic_lebaran'              => ['teknisi' => 140000, 'kenek' => 60000],   // 2× dsp_lebaran × 2
        'skkb_lebaran'                       => ['teknisi' => 98000,  'kenek' => 42000],
        'skkb_panoramic_lebaran'             => ['teknisi' => 158000, 'kenek' => 72000],   // 2× skkb_panoramic
        'fullset_lebaran'                    => ['teknisi' => 140000, 'kenek' => 60000],
        'full_panoramic_lebaran'             => ['teknisi' => 200000, 'kenek' => 90000],   // 2× full_panoramic
        'fullset_ekstra_lebaran'             => ['teknisi' => 210000, 'kenek' => 90000],
        'ekstra_panoramic_lebaran'           => ['teknisi' => 270000, 'kenek' => 120000],  // 2× ekstra_panoramic
        'fullset_ekstra_plus_lebaran'        => ['teknisi' => 280000, 'kenek' => 120000],
        'fullset_ekstra_plus_panoramic_lebaran' => ['teknisi' => 340000, 'kenek' => 150000], // 2× ekstra_plus_panoramic

    ],


    /*
    |--------------------------------------------------------------------------
    | Komisi Marketing
    |--------------------------------------------------------------------------
    | 1 = 4K ALPHA PRO
    | 2 = NOTCH UV 400 (tarif baru)
    | 3 = BLACK MASTER (tarif lama kategori 2)
    | ID mengikuti tabel category_products — cek ID di production sebelum deploy.
    */

    'marketing' => [
        1 => [
            'fullset'          => 120000,
            'full_panoramic'   => 120000, // same as fullset
            'ekstra_panoramic' => 120000, // same as fullset
            'dsp'              => 70000,
            'dsp_panoramic'    => 140000, // dsp × 2 (kaca depan + panoramic)
            'skkb'             => 100000,
        ],

        2 => [
            'fullset'          => 80000,
            'full_panoramic'   => 80000,  // same as fullset
            'ekstra_panoramic' => 80000,  // same as fullset
            'dsp'              => 40000,
            'dsp_panoramic'    => 80000,  // dsp × 2
            'skkb'             => 60000,
        ],

        3 => [
            'fullset'          => 70000,
            'full_panoramic'   => 70000,  // same as fullset
            'ekstra_panoramic' => 70000,  // same as fullset
            'dsp'              => 30000,
            'dsp_panoramic'    => 60000,  // dsp × 2
            'skkb'             => 50000,
        ],

        'default' => [
            'fullset'          => 0,
            'full_panoramic'   => 0,
            'ekstra_panoramic' => 0,
            'dsp'              => 0,
            'dsp_panoramic'    => 0,
            'skkb'             => 0,
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Komisi Teknisi Gedung (per meter)
    |--------------------------------------------------------------------------
    | Key = category_product_building.id. Tanpa kenek: pool = Σ(meter × tarif),
    | dibagi rata ke teknisi yang ditugaskan di customer gedung.
    | 1 = 4K ALPHA PRO, 2 = NOTCH UV 400, 3 = BLACK MASTER, 4 = FLEX ONE WAY.
    | Sunblast (Rp10.000/m) belum ada sebagai kategori — tambahkan kategorinya
    | di category_product_building lalu isi ID-nya di sini.
    */

    'building_per_meter' => [
        1 => 40000,
        2 => 30000,
        3 => 25000,
        4 => 15000,
    ],


    /*
    |--------------------------------------------------------------------------
    | Komisi Marketing Gedung (per meter)
    |--------------------------------------------------------------------------
    | Key = category_product_building.id. Dasar = SELURUH meter produk customer
    | gedung (bukan meter alokasi teknisi). Dibayar ke marketing_id customer gedung
    | saat invoice lunas. Sunblast (Rp10.000/m) belum ada sebagai kategori.
    */

    'building_marketing_per_meter' => [
        1 => 25000,   // 4K Alpha Pro
        2 => 20000,   // Notch UV400
        3 => 15000,   // Black Master
        4 => 10000,   // Flex One Way
    ],


    /*
    |--------------------------------------------------------------------------
    | Prioritas Kategori Marketing
    |--------------------------------------------------------------------------
    | Bila satu customer punya beberapa kategori produk, dipakai kategori yang
    | muncul lebih dulu di daftar ini (tarif terendah lebih dulu: BM, Notch, 4K).
    */

    'marketing_category_priority' => [3, 2, 1],


    /*
    |--------------------------------------------------------------------------
    | Tipe upgrade untuk mobil ke-2 di hari yang sama
    |--------------------------------------------------------------------------
    */

    'second_car_upgrade' => 'fullset_ekstra',
    'third_car_upgrade'  => 'fullset_ekstra_plus',


    /*
    |--------------------------------------------------------------------------
    | Komisi PPF (Paint Protection Film)
    |--------------------------------------------------------------------------
    |
    | teknisi_rate  : % dari total_price invoice → untuk tukang_id instalasi
    | detailer_rate : % dari total_price invoice → untuk kenek_id instalasi
    | marketing_fixed : nominal tetap → untuk marketing_id customer PPF
    |
    */

    'ppf' => [
        'teknisi_rate'    => 0.03,    // 3%
        'detailer_rate'   => 0.02,    // 2%
        'admin_rate'      => 0.02,    // 2% — employees with ppf_admin_commission = true
        'marketing_fixed' => 500000,  // Rp500.000
    ],

];
