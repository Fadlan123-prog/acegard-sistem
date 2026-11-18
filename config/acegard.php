<?php

return [
    // Nomor WhatsApp resmi (format internasional, tanpa +). Ubah lewat .env: ACEGARD_WHATSAPP
    'whatsapp' => preg_replace('/\D+/', '', (string) env('ACEGARD_WHATSAPP', '6282121212093')),
    'email'    => env('ACEGARD_EMAIL', 'acegardindonesia@gmail.com'),
    'address'  => 'Jl. KH. Hasyim Ashari, RT.007/RW.002, Nerogtog, Kec. Cipondoh, Kota Tangerang, Banten 15146',
];
