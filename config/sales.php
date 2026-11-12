<?php

return [
    // Batas diskon maksimal (persen dari harga produk) yang boleh diinput sales.
    'max_discount_percent' => (float) env('SALES_MAX_DISCOUNT_PERCENT', 20),
];
