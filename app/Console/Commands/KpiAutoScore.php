<?php

namespace App\Console\Commands;

use App\Models\KpiScore;
use App\Models\KpiTarget;
use App\Services\KpiMetrics;
use Illuminate\Console\Command;

class KpiAutoScore extends Command
{
    protected $signature = 'kpi:auto-score {--month=} {--year=}';

    protected $description = 'Hitung skor KPI otomatis dari data operasional untuk target ber-sumber otomatis';

    public function handle(): int
    {
        $month = (int) ($this->option('month') ?: now()->month);
        $year  = (int) ($this->option('year') ?: now()->year);
        $count = 0;

        $targets = KpiTarget::where('period_month', $month)
            ->where('period_year', $year)
            ->where('source', '!=', 'manual')
            ->get();

        foreach ($targets as $target) {
            foreach (KpiMetrics::resolve($target->source, $month, $year) as $employeeId => $value) {
                KpiScore::updateOrCreate(
                    ['employee_id' => $employeeId, 'kpi_target_id' => $target->id],
                    ['actual_value' => round($value, 2), 'is_auto' => true],
                );
                $count++;
            }
        }

        $this->info("KPI otomatis {$month}/{$year}: {$count} skor diperbarui dari {$targets->count()} target.");

        return self::SUCCESS;
    }
}
