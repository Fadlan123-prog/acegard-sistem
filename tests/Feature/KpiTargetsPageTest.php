<?php

namespace Tests\Feature;

use App\Models\KpiTarget;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiTargetsPageTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return $this->userWithRole('owner', Branch::factory()->create());
    }

    public function test_page_renders_period_and_weight_state(): void
    {
        KpiTarget::create(['division'=>'teknisi','metric_key'=>'a','metric_label'=>'Rework','target_value'=>5,'weight'=>60,'direction'=>'lower_better','period_month'=>11,'period_year'=>2026]);

        $this->actingAs($this->owner())
            ->get('/kpi/targets?month=11&year=2026')
            ->assertOk()
            ->assertSee('November 2026')
            ->assertSee('Kurang 40%')
            ->assertSee('Rework');
    }

    public function test_empty_period_and_bad_params_are_clamped(): void
    {
        $this->actingAs($this->owner())
            ->get('/kpi/targets?month=99&year=1900')
            ->assertOk()
            ->assertSee('Belum ada target KPI untuk Desember 2026');
    }
}
