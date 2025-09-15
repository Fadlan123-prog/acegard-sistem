<?php

return [
    'fixed' => [
        'fullset'               => ['Teknisi'=>70000,  'Kenek'=>30000],
        'fullset_ekstra'        => ['Teknisi'=>105000, 'Kenek'=>45000],
        'fullset_ekstra_plus'   => ['Teknisi'=>140000, 'Kenek'=>60000],
        'full_panoramic'        => ['Teknisi'=>100000, 'Kenek'=>45000],
        'ekstra_panoramic'      => ['Teknisi'=>135000, 'Kenek'=>60000],
        'dsp'                   => ['Teknisi'=>35000,  'Kenek'=>15000],
        'skkb'                  => ['Teknisi'=>49000,  'Kenek'=>21000],
    ],

    'marketing' => [
        1 => [ // 4K ALPHA PRO
            'fullset' => 100000,
            'dsp'     => 70000,
            'skkb'    => 100000,
        ],
        2 => [ // NOTCH UV 400
            'fullset' => 70000,
            'dsp'     => 30000,
            'skkb'    => 50000,
        ],
        // default kategori lain: tidak ada komisi
        'default' => [
            'fullset' => 0,
            'dsp'     => 0,
            'skkb'    => 0,
        ],
    ],

    'marketing_category_priority' => [2, 1], // 2: Notch UV 400, 1: 4K Alpha Pro
];
