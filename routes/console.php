<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run every day at 07:00 — sends hutang/piutang/warranty/stock alerts
Schedule::command('notify:scheduled')->dailyAt('07:00');
