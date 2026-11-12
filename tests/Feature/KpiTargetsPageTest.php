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

    public function test_all_kpi_pages_render_with_shared_ui(): void
    {
        $owner = $this->userWithRole('owner', Branch::factory()->create());
        KpiTarget::create(['division'=>'teknisi','metric_key'=>'a','metric_label'=>'Rework','target_value'=>5,'weight'=>100,'direction'=>'lower_better','period_month'=>11,'period_year'=>2026]);

        foreach (['/kpi/targets', '/kpi/targets/create', '/kpi/scores'] as $url) {
            $r = $this->actingAs($owner)->get($url.'?month=11&year=2026')->assertOk();
            $this->assertStringNotContainsString('kt-', $r->getContent(), $url);
        }

        $this->actingAs($owner)->get('/kpi/scores?month=11&year=2026')->assertSee('Filter')->assertSee('Reset');

        // Skor Saya hanya untuk akun yang terhubung ke karyawan.
        $this->actingAs($owner)->get('/kpi/my-scorecard')->assertNotFound();

        $branch = Branch::factory()->create();
        $emp = \App\Models\Employee::factory()->create(['branch_id' => $branch->id]);
        $u = $this->userWithRole('marketing', $branch);
        $u->update(['employee_id' => $emp->id]);
        $this->actingAs($u)->get('/kpi/my-scorecard')->assertOk()->assertSee('Skor KPI Saya');
    }
}
