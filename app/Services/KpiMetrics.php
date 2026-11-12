<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Katalog metrik KPI otomatis. Tiap metrik mengembalikan nilai aktual per
 * karyawan (employee_id => nilai) untuk satu bulan, langsung dari data
 * operasional. Hanya karyawan yang punya aktivitas pada periode itu yang muncul.
 */
class KpiMetrics
{
    /** kode => [label, satuan, closure(month, year): array<int, float>] */
    public static function catalog(): array
    {
        $month = fn ($col) => fn ($q, $m, $y) => $q->whereMonth($col, $m)->whereYear($col, $y);

        $sum = function (string $table, string $dateCol, string $expr, ?string $where = null) {
            return function (int $m, int $y) use ($table, $dateCol, $expr, $where) {
                return DB::table($table)
                    ->whereMonth($dateCol, $m)->whereYear($dateCol, $y)
                    ->when($where, fn ($q) => $q->whereRaw($where))
                    ->whereNotNull('employee_id')
                    ->groupBy('employee_id')
                    ->select('employee_id', DB::raw("$expr as v"))
                    ->pluck('v', 'employee_id')
                    ->map(fn ($v) => (float) $v)->all();
            };
        };

        $leads = function (string $expr, ?string $where = null) {
            return function (int $m, int $y) use ($expr, $where) {
                return DB::table('leads')
                    ->whereMonth('created_at', $m)->whereYear('created_at', $y)
                    ->whereNotNull('assigned_to')
                    ->when($where, fn ($q) => $q->whereRaw($where))
                    ->groupBy('assigned_to')
                    ->select('assigned_to', DB::raw("$expr as v"))
                    ->pluck('v', 'assigned_to')
                    ->map(fn ($v) => (float) $v)->all();
            };
        };

        return [
            'content.video_published' => ['Video dipublikasi', 'video', $sum('content_posts', 'published_at', 'COUNT(*)')],
            'content.total_views'     => ['Total views konten', 'views', $sum('content_posts', 'published_at', 'SUM(views)')],
            'content.fyp_videos'      => ['Video FYP', 'video', $sum('content_posts', 'published_at', 'COUNT(*)', 'is_fyp = 1')],
            'content.leads'           => ['Leads dari konten', 'leads', $sum('content_posts', 'published_at', 'SUM(leads_generated)')],
            'live.hours'              => ['Jam live', 'jam', $sum('live_sessions', 'session_date', 'SUM(duration_minutes)/60')],
            'live.closing'            => ['Closing dari live', 'closing', $sum('live_sessions', 'session_date', 'SUM(closing_count)')],
            'live.leads'              => ['Leads dari live', 'leads', $sum('live_sessions', 'session_date', 'SUM(leads_generated)')],
            'leads.total'             => ['Leads ditangani', 'leads', $leads('COUNT(*)')],
            'leads.followed_up'       => ['Leads di-follow up', 'leads', $leads('COUNT(*)', "status IN ('follow_up','quoted','won')")],
            'leads.won'               => ['Leads closing', 'leads', $leads('COUNT(*)', "status = 'won'")],
            'leads.closing_rate'      => ['Closing rate (%)', '%', $leads('ROUND(SUM(status = \'won\') / COUNT(*) * 100, 2)')],
        ];
    }

    public static function options(): array
    {
        return array_map(fn ($m) => $m[0] . ' (' . $m[1] . ')', self::catalog());
    }

    public static function has(string $code): bool
    {
        return isset(self::catalog()[$code]);
    }

    public static function resolve(string $code, int $month, int $year): array
    {
        $def = self::catalog()[$code] ?? null;

        return $def ? ($def[2])($month, $year) : [];
    }
}
