<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\KpiScore;
use App\Models\KpiTarget;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KpiAutoScoreTest extends TestCase
{
    use RefreshDatabase;

    private function target(string $source, array $extra = []): KpiTarget
    {
        return KpiTarget::create($extra + [
            'division' => 'konten', 'metric_key' => str_replace('.', '_', $source), 'source' => $source,
            'metric_label' => 'Auto ' . $source, 'target_value' => 10, 'weight' => 50,
            'direction' => 'higher_better', 'period_month' => 11, 'period_year' => 2026,
        ]);
    }

    public function test_command_scores_content_and_leads_and_is_idempotent(): void
    {
        $branch = Branch::factory()->create();
        $emp = Employee::factory()->create(['branch_id' => $branch->id]);

        foreach ([[true, 1000, 5], [false, 500, 2]] as [$fyp, $views, $leads]) {
            DB::table('content_posts')->insert([
                'branch_id' => $branch->id, 'employee_id' => $emp->id, 'platform' => 'tiktok', 'title' => 'v',
                'published_at' => '2026-11-05', 'views' => $views, 'leads_generated' => $leads, 'is_fyp' => $fyp,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        foreach (['won', 'won', 'new', 'follow_up'] as $st) {
            Lead::create(['branch_id' => $branch->id, 'name' => 'x', 'source' => 'walk_in', 'category' => 'car_film',
                'status' => $st, 'assigned_to' => $emp->id, 'created_at' => '2026-11-06', 'updated_at' => '2026-11-06']);
        }

        $views = $this->target('content.total_views');
        $fyp   = $this->target('content.fyp_videos');
        $rate  = $this->target('leads.closing_rate');
        $fup   = $this->target('leads.followed_up');
        $manual = $this->target('manual', ['metric_key' => 'm']);

        $this->artisan('kpi:auto-score', ['--month' => 11, '--year' => 2026])->assertExitCode(0);
        $this->artisan('kpi:auto-score', ['--month' => 11, '--year' => 2026])->assertExitCode(0);

        $val = fn ($t) => (float) KpiScore::where('kpi_target_id', $t->id)->where('employee_id', $emp->id)->value('actual_value');
        $this->assertSame(1500.0, $val($views));
        $this->assertSame(1.0, $val($fyp));
        $this->assertSame(50.0, $val($rate));
        $this->assertSame(3.0, $val($fup));
        $this->assertSame(4, KpiScore::count());
        $this->assertSame(0, KpiScore::where('kpi_target_id', $manual->id)->count());
        $this->assertTrue((bool) KpiScore::first()->is_auto);
    }

    public function test_auto_target_cannot_be_scored_manually_and_unknown_source_rejected(): void
    {
        $owner = $this->userWithRole('owner', Branch::factory()->create());
        $emp = Employee::factory()->create();
        $auto = $this->target('live.hours');

        $this->actingAs($owner)->post('/kpi/scores', [
            'employee_id' => $emp->id, 'kpi_target_id' => $auto->id, 'actual_value' => 9,
        ])->assertStatus(422);

        $this->actingAs($owner)->post('/kpi/targets', [
            'division' => 'konten', 'metric_key' => 'zz', 'source' => 'bogus.metric', 'metric_label' => 'Z',
            'target_value' => 1, 'weight' => 10, 'direction' => 'higher_better', 'period_month' => 11, 'period_year' => 2026,
        ])->assertSessionHasErrors('source');

        $this->actingAs($owner)->post('/kpi/targets', [
            'division' => 'konten', 'metric_key' => 'ok', 'source' => 'live.hours', 'metric_label' => 'Jam',
            'target_value' => 1, 'weight' => 10, 'direction' => 'higher_better', 'period_month' => 11, 'period_year' => 2026,
        ])->assertRedirect();
        $this->assertSame('live.hours', KpiTarget::where('metric_key', 'ok')->value('source'));

        $this->actingAs($owner)->get('/kpi/targets/create')->assertSee('Otomatis — Jam live');
        $this->actingAs($owner)->get('/kpi/targets?month=11&year=2026')->assertSee('Otomatis');
    }
}
