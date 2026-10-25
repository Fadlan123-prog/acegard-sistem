<?php

namespace Tests\Feature;

use App\Models\DevelopmentAnnualPlan;
use App\Models\DevelopmentReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevelopmentModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_development_role_can_create_periodic_report_and_owner_can_read_it(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $dev = $this->userWithRole('development');
        $owner = $this->userWithRole('owner');

        $this->actingAs($dev)->post(route('development-reports.store'), [
            'title'        => 'Laporan Q1 2026',
            'content'      => 'Progres ekspansi cabang baru berjalan sesuai rencana.',
            'period_start' => '2026-01-01',
            'period_end'   => '2026-03-31',
        ])->assertRedirect();

        $report = DevelopmentReport::first();
        $this->assertSame($dev->id, $report->created_by);

        // Owner can read it, even though they didn't create it
        $this->actingAs($owner)->get(route('development-reports.show', $report))
            ->assertSuccessful()
            ->assertSee('Laporan Q1 2026');
    }

    public function test_annual_plan_with_initiatives_full_flow(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $dev = $this->userWithRole('development');

        $this->actingAs($dev)->post(route('development-plans.store'), [
            'year'    => 2027,
            'summary' => 'Fokus ekspansi PPF ke 3 kota baru.',
        ])->assertRedirect();

        $plan = DevelopmentAnnualPlan::where('year', 2027)->first();
        $this->assertNotNull($plan);

        $this->actingAs($dev)->post(route('development-plans.initiatives.store', $plan), [
            'title'       => 'Buka cabang Surabaya',
            'target_date' => '2027-06-30',
        ])->assertRedirect();

        $plan->refresh();
        $this->assertSame(1, $plan->initiatives()->count());
        $initiative = $plan->initiatives()->first();
        $this->assertSame('planned', $initiative->status);
        $this->assertSame($plan->id, $initiative->plan_id);

        $this->actingAs($dev)->put(
            route('development-plans.initiatives.status', [$plan, $initiative]),
            ['status' => 'in_progress']
        )->assertRedirect();

        $initiative->refresh();
        $this->assertSame('in_progress', $initiative->status);
    }

    public function test_duplicate_year_annual_plan_is_rejected(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $dev = $this->userWithRole('development');

        DevelopmentAnnualPlan::create(['created_by' => $dev->id, 'year' => 2027, 'summary' => 'Existing']);

        $this->actingAs($dev)->post(route('development-plans.store'), [
            'year'    => 2027,
            'summary' => 'Duplicate attempt',
        ])->assertSessionHasErrors('year');
    }
}
