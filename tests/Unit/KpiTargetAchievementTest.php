<?php

namespace Tests\Unit;

use App\Models\KpiTarget;
use PHPUnit\Framework\TestCase;

/** Rumus pencapaian KPI dari "Kartu KPI per Divisi": aktual/target, dibalik untuk KPI lower_better, dibatasi 100%. */
class KpiTargetAchievementTest extends TestCase
{
    public function test_higher_better_is_capped_at_100_percent(): void
    {
        $target = new KpiTarget(['target_value' => 80, 'direction' => 'higher_better']);

        $this->assertSame(100.0, $target->achievementPercent(120));
        $this->assertSame(75.0, $target->achievementPercent(60));
    }

    public function test_lower_better_is_100_within_target_and_inverted_above_it(): void
    {
        $target = new KpiTarget(['target_value' => 10, 'direction' => 'lower_better']);

        $this->assertSame(100.0, $target->achievementPercent(8));
        $this->assertSame(50.0, $target->achievementPercent(20));
    }
}
