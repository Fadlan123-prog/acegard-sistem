<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardDonutChartsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_dashboard_renders_its_donut(): void
    {
        $branch = Branch::factory()->create();
        $emp = Employee::factory()->create(['branch_id' => $branch->id]);
        Lead::create(['branch_id' => $branch->id, 'name' => 'a', 'phone' => '1', 'source' => 'walk_in', 'ad_channel' => 'meta',
            'category' => 'car_film', 'status' => 'won', 'assigned_to' => $emp->id]);
        DB::table('ad_spend_entries')->insert(['branch_id' => $branch->id, 'channel' => 'meta', 'spend_date' => now()->toDateString(),
            'amount' => 1000, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('content_posts')->insert(['branch_id' => $branch->id, 'employee_id' => $emp->id, 'platform' => 'tiktok', 'title' => 'v',
            'published_at' => now()->toDateString(), 'views' => 10, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('live_sessions')->insert(['branch_id' => $branch->id, 'employee_id' => $emp->id, 'session_date' => now()->toDateString(),
            'duration_minutes' => 120, 'closing_count' => 1, 'created_at' => now(), 'updated_at' => now()]);

        $cases = [
            ['owner', '/owner/dashboard', 'Porsi Omzet per Kategori'],
            ['marketing', '/advertising/dashboard', 'Porsi Spend per Channel'],
            ['customer_service', '/cs/dashboard', 'Status Leads'],
            ['marketing', '/marketing/dashboard', 'Komposisi Status Lead Saya'],
            ['owner', '/content/dashboard', 'Porsi Views per Creator'],
            ['owner', '/live-sessions', 'Porsi Jam Live per Host'],
        ];

        foreach ($cases as [$role, $url, $id]) {
            $u = $this->userWithRole($role, $branch);
            $this->actingAs($u)->get($url)->assertOk()->assertSee($id);
        }
    }

    public function test_empty_dashboard_shows_empty_state_instead_of_chart(): void
    {
        $o = $this->userWithRole('owner', Branch::factory()->create());

        $this->actingAs($o)->get('/live-sessions')->assertOk()->assertDontSee('donutLiveHours', false);
    }

    public function test_chart_container_and_script_render_when_data_exists(): void
    {
        $branch = Branch::factory()->create();
        $emp = Employee::factory()->create(['branch_id' => $branch->id]);
        DB::table('live_sessions')->insert(['branch_id' => $branch->id, 'employee_id' => $emp->id, 'session_date' => now()->toDateString(),
            'duration_minutes' => 120, 'closing_count' => 1, 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($this->userWithRole('owner', $branch))->get('/live-sessions')
            ->assertOk()->assertSee('id="donutLiveHours"', false)->assertSee("type: 'donut'", false);
    }
}
