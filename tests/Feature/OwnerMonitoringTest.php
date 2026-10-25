<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CreativeTask;
use App\Models\DevelopmentReport;
use App\Models\Employee;
use App\Models\EmployeeClaim;
use App\Models\ExportOrder;
use App\Models\ImportOrder;
use App\Models\InternationalPartner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_monitoring_page_aggregates_data_from_every_module(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);

        $owner = $this->userWithRole('owner');
        $branch = Branch::factory()->create();

        // A pending employee claim
        $employee = Employee::factory()->create(['branch_id' => $branch->id]);
        $teknisi = User::factory()->create(['employee_id' => $employee->id]);
        $teknisi->assignRole('teknisi');
        $claim = EmployeeClaim::create([
            'employee_id' => $employee->id, 'branch_id' => $branch->id,
            'type' => 'bensin', 'amount' => 50000, 'status' => 'pending_approval',
        ]);
        $claim->submitForApproval('employee_claim', $teknisi->id);

        // A creative task in progress
        $strategist = $this->userWithRole('strategi_pengembangan');
        $designer = $this->userWithRole('designer');
        CreativeTask::create([
            'assigned_by' => $strategist->id, 'assigned_to' => $designer->id,
            'title' => 'Banner promo', 'brief' => 'Test', 'priority' => 'normal', 'status' => 'in_progress',
        ]);

        // An import order
        $supplier = InternationalPartner::create(['type' => 'supplier', 'name' => 'X Co', 'country' => 'Japan', 'default_currency' => 'JPY']);
        ImportOrder::create([
            'international_partner_id' => $supplier->id, 'order_number' => 'IMP-TEST-001',
            'currency' => 'JPY', 'exchange_rate' => 105, 'subtotal_foreign' => 1000,
            'total_cost_idr' => 105000, 'order_date' => now()->toDateString(),
            'status' => 'shipped', 'created_by' => $owner->id,
        ]);

        // An export order
        $buyer = InternationalPartner::create(['type' => 'buyer', 'name' => 'Y Co', 'country' => 'USA', 'default_currency' => 'USD']);
        ExportOrder::create([
            'international_partner_id' => $buyer->id, 'order_number' => 'EXP-TEST-001',
            'currency' => 'USD', 'exchange_rate' => 15800, 'subtotal_foreign' => 500,
            'total_revenue_idr' => 7900000, 'order_date' => now()->toDateString(),
            'status' => 'confirmed', 'created_by' => $owner->id,
        ]);

        // A development report
        $dev = $this->userWithRole('development');
        DevelopmentReport::create([
            'created_by' => $dev->id, 'title' => 'Laporan Test', 'content' => 'Isi laporan',
            'period_start' => now()->subMonth(), 'period_end' => now(),
        ]);

        $response = $this->actingAs($owner)->get(route('owner-monitoring.index'));

        $response->assertSuccessful();
        $response->assertSee('Klaim Karyawan');
        $response->assertSee('Laporan Test'); // latest development report title is shown directly
        $response->assertSee('Export & Import', false);
        $response->assertSee('Team Creative');
    }

    public function test_non_owner_cannot_access_monitoring_page(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $marketing = $this->userWithRole('marketing');

        $this->actingAs($marketing)->get(route('owner-monitoring.index'))->assertForbidden();
    }
}
