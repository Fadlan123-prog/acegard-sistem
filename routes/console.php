<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run every day at 07:00 — sends hutang/piutang/warranty/stock alerts
Schedule::command('notify:scheduled')->dailyAt('07:00');

// Every 1 January — creates the new year's health-claim balance row per
// active employee (carries forward the previous year's allocation; history
// is never overwritten, see ResetHealthBalances).
Schedule::command('claims:reset-health-balances')->yearlyOn(1, 1, '00:30');

// Backup database harian 02:00 (lihat docs/DEPLOY-HOSTINGER.md untuk salinan off-server & uji restore).
Schedule::command('backup:database')->dailyAt('02:00')->withoutOverlapping();
