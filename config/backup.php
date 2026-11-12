<?php

// Dibaca lewat config() agar tetap berlaku setelah `php artisan config:cache`.
return [
    'mysqldump_path' => env('MYSQLDUMP_PATH', 'mysqldump'),
    'keep_days'      => (int) env('BACKUP_KEEP_DAYS', 14),
    'copy_disk'      => env('BACKUP_COPY_DISK'),
];
