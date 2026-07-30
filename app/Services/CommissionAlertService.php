<?php

namespace App\Services;

use App\Http\Controllers\CommissionPeriodController;
use App\Models\User;
use App\Notifications\CommissionPeriodDueAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CommissionAlertService
{
    private const ALERT_DAYS_BEFORE = 5; // mulai notifikasi H-5 sebelum tanggal 15

    /**
     * Cek kondisi alert dan kirim notifikasi jika perlu.
     * Di-throttle dengan cache agar tidak kirim berulang-ulang dalam satu hari.
     */
    public function checkAndNotify(int $branchId): void
    {
        $cacheKey = "commission_alert_sent_{$branchId}_" . now()->format('Y-m-d');

        if (Cache::has($cacheKey)) {
            return; // sudah kirim hari ini
        }

        $now = now();

        // Hitung periode berjalan
        [$periodStart, $periodEnd] = CommissionPeriodController::currentPeriod();
        $daysUntilDue = (int) $now->copy()->startOfDay()->diffInDays($periodEnd->copy()->startOfDay(), false);

        if ($daysUntilDue > self::ALERT_DAYS_BEFORE || $daysUntilDue < 0) {
            return; // belum waktunya alert atau sudah lewat
        }

        // Hitung invoice yang sudah pelunasan tapi belum rilis komisi
        $unreleasedCount = DB::table('invoices as i')
            ->join('customers as c', 'c.id', '=', 'i.customer_id')
            ->where('c.branch_id', $branchId)
            ->whereNotNull('i.pelunasan_at')
            ->whereNull('i.commission_released_at')
            ->whereDate('i.created_at', '>=', $periodStart->toDateString())
            ->whereDate('i.created_at', '<=', $periodEnd->toDateString())
            ->count();

        // Hitung karyawan dengan komisi belum terbayar di periode ini
        $unpaidCount = DB::table('commissions as c')
            ->join('employees as e', 'e.id', '=', 'c.employees_id')
            ->where('e.branch_id', $branchId)
            ->where('c.status', 'eligible')
            ->whereDate('c.created_at', '>=', $periodStart->toDateString())
            ->whereDate('c.created_at', '<=', $periodEnd->toDateString())
            ->distinct('c.employees_id')
            ->count('c.employees_id');

        if ($unreleasedCount === 0 && $unpaidCount === 0) {
            Cache::put($cacheKey, true, now()->endOfDay());
            return; // tidak ada yang perlu dinotifikasi
        }

        // Kirim ke superadmin
        $admins = User::whereHas('roles', fn($q) => $q->whereIn('name', ['superadmin', 'admin']))
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new CommissionPeriodDueAlert(
            periodStart:     $periodStart,
            periodEnd:       $periodEnd,
            unpaidCount:     $unpaidCount,
            unreleasedCount: $unreleasedCount,
            daysUntilDue:    $daysUntilDue,
        ));

        // Tandai sudah kirim hari ini
        Cache::put($cacheKey, true, now()->endOfDay());
    }
}
