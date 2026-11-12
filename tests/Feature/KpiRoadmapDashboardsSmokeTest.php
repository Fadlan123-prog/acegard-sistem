<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Smoke test untuk dashboard-dashboard baru (roadmap KPI 2026-11): harus 200 dan tidak bocor lintas cabang. */
class KpiRoadmapDashboardsSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_cs_dashboard_only_counts_leads_from_its_own_branch(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $cs = $this->userWithRole('customer_service', $branchA);

        Lead::create(['branch_id' => $branchA->id, 'name' => 'Lead A', 'source' => 'walk_in', 'category' => 'car_film', 'status' => 'new']);
        Lead::create(['branch_id' => $branchB->id, 'name' => 'Lead B', 'source' => 'walk_in', 'category' => 'car_film', 'status' => 'new']);

        $this->actingAs($cs)->get(route('cs.dashboard'))
            ->assertOk()
            ->assertSee('Lead A')
            ->assertDontSee('Lead B');
    }

    public function test_advertising_dashboard_loads_for_marketing(): void
    {
        $branch = Branch::factory()->create();
        $marketing = $this->userWithRole('marketing', $branch);

        $this->actingAs($marketing)->get(route('advertising.dashboard'))->assertOk();
        $this->actingAs($marketing)->get(route('advertising.spend.create'))->assertOk();
    }

    public function test_content_and_live_dashboards_load_for_creative_team(): void
    {
        $branch = Branch::factory()->create();
        $employee = Employee::factory()->create(['branch_id' => $branch->id]);
        $designer = $this->userWithRole('designer', $branch);
        $designer->update(['employee_id' => $employee->id]);

        $this->actingAs($designer)->get(route('content.dashboard'))->assertOk();
        $this->actingAs($designer)->get(route('live-sessions.index'))->assertOk();
    }

    public function test_kpi_target_and_score_flow(): void
    {
        $branch   = Branch::factory()->create();
        $employee = Employee::factory()->create(['branch_id' => $branch->id]);
        $admin    = $this->userWithRole('superadmin', $branch);

        $this->actingAs($admin)->post(route('kpi.targets.store'), [
            'division' => 'customer_service', 'metric_key' => 'balas_chat', 'metric_label' => 'Balas chat ≤5 menit',
            'target_value' => 100, 'weight' => 30, 'direction' => 'higher_better',
            'period_month' => now()->month, 'period_year' => now()->year,
        ])->assertRedirect();

        $target = \App\Models\KpiTarget::firstOrFail();

        $this->actingAs($admin)->post(route('kpi.scores.store'), [
            'employee_id' => $employee->id, 'kpi_target_id' => $target->id, 'actual_value' => 92,
        ])->assertRedirect();

        $this->assertDatabaseHas('kpi_scores', ['employee_id' => $employee->id, 'kpi_target_id' => $target->id]);
    }
}
