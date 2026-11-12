<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Services\ExecutiveSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerExecutiveOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_dashboard_shows_executive_summary(): void
    {
        $owner = $this->userWithRole('owner', Branch::factory()->create());

        $this->actingAs($owner)->get('/owner/dashboard?month=11&year=2026')
            ->assertOk()
            ->assertSee('Ringkasan Eksekutif')
            ->assertSee('Total Advertising Spend')
            ->assertSee('Omzet per Kategori');
    }

    public function test_summary_on_empty_data_has_no_division_errors(): void
    {
        $s = app(ExecutiveSummaryService::class)->summary(11, 2026);

        $this->assertSame(0, $s['revenue']['total']);
        $this->assertNull($s['growth_pct']);
        $this->assertNull($s['roas']);
        $this->assertCount(6, $s['trend']);
    }

    public function test_advertising_funnel_shows_cpl_followup_and_repeat_order(): void
    {
        $branch = Branch::factory()->create();
        $m = $this->userWithRole('marketing', $branch);
        foreach ([1, 2] as $i) {
            \App\Models\Lead::create(['branch_id' => $branch->id, 'name' => "L$i", 'phone' => '0812', 'source' => 'walk_in', 'category' => 'car_film', 'status' => 'won']);
        }

        $this->actingAs($m)->get(route('advertising.dashboard'))
            ->assertOk()
            ->assertSee('CPL (biaya per lead)')
            ->assertSee('Repeat Order')
            ->assertDontSee('Qualified');
    }
}
