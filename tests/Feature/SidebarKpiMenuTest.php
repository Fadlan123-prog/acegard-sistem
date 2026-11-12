<?php

namespace Tests\Feature;

use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarKpiMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sees_all_dashboard_links_once(): void
    {
        $o = $this->userWithRole('owner', Branch::factory()->create());
        $html = $this->actingAs($o)->get('/kpi/targets')->assertOk()->getContent();

        foreach (['Ringkasan Eksekutif', 'Dashboard Advertising', 'Dashboard Konten', 'TikTok Live', 'Skor KPI Karyawan'] as $label) {
            $this->assertStringContainsString($label, $html);
        }
        $this->assertSame(1, substr_count($html, '>Target KPI</a>'));
    }

    public function test_admin_sees_kpi_but_not_owner_only_dashboards(): void
    {
        $a = $this->userWithRole('admin', Branch::factory()->create());
        $html = $this->actingAs($a)->get('/kpi/targets')->assertOk()->getContent();

        $this->assertStringContainsString('Skor KPI Karyawan', $html);
        $this->assertStringNotContainsString('Ringkasan Eksekutif', $html);
        $this->assertStringNotContainsString('Dashboard Advertising', $html);
    }
}
