<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeHealthBalance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The health-balance backend (EmployeeHealthBalance, claims:reset-health-balances,
 * the deduction in EmployeeClaim::onApprovalDecision()) existed but nothing ever
 * created the balance rows or set a real allocation — this admin page is the
 * missing piece that lets superadmin/admin see and override each teknisi/kenek's
 * yearly allowance.
 */
class EmployeeHealthBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_self_heals_missing_balance_rows_using_the_configured_default(): void
    {
        config(['hr.default_health_allocation' => 20000000]);

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        Employee::factory()->create(['branch_id' => $branch->id, 'job_position' => 'Teknisi', 'is_active' => true]);

        $this->assertDatabaseCount('employee_health_balances', 0);

        $response = $this->actingAs($admin)->get(route('employee-health-balances.index', ['year' => 2026]));

        $response->assertSuccessful();
        $this->assertDatabaseCount('employee_health_balances', 1);
        $this->assertDatabaseHas('employee_health_balances', ['year' => 2026, 'allocated_amount' => 20000000, 'used_amount' => 0]);
    }

    public function test_admin_can_override_allocation_for_a_specific_employee(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $employee = Employee::factory()->create(['branch_id' => $branch->id, 'job_position' => 'Kenek', 'is_active' => true]);
        $balance = EmployeeHealthBalance::create([
            'employee_id' => $employee->id, 'year' => 2026, 'allocated_amount' => 20000000, 'used_amount' => 0,
        ]);

        $this->actingAs($admin)
            ->put(route('employee-health-balances.update', $balance), ['allocated_amount' => 30000000])
            ->assertRedirect();

        $balance->refresh();
        $this->assertSame(30000000, $balance->allocated_amount);
    }

    public function test_cannot_override_a_balance_belonging_to_another_branch(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $ownBranch = Branch::factory()->create();
        $otherBranch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $ownBranch);

        $employee = Employee::factory()->create(['branch_id' => $otherBranch->id, 'job_position' => 'Teknisi', 'is_active' => true]);
        $balance = EmployeeHealthBalance::create([
            'employee_id' => $employee->id, 'year' => 2026, 'allocated_amount' => 20000000, 'used_amount' => 0,
        ]);

        $this->actingAs($admin)
            ->put(route('employee-health-balances.update', $balance), ['allocated_amount' => 99999999])
            ->assertForbidden();

        $balance->refresh();
        $this->assertSame(20000000, $balance->allocated_amount);
    }

    public function test_teknisi_dashboard_shows_the_current_years_remaining_health_balance(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $branch = Branch::factory()->create();
        $employee = Employee::factory()->create(['branch_id' => $branch->id, 'job_position' => 'Teknisi', 'is_active' => true]);
        $teknisi = User::factory()->create(['employee_id' => $employee->id]);
        $teknisi->assignRole('teknisi');

        EmployeeHealthBalance::create([
            'employee_id' => $employee->id, 'year' => now()->year,
            'allocated_amount' => 20000000, 'used_amount' => 5000000,
        ]);

        $response = $this->actingAs($teknisi)->get(route('teknisi.dashboard'));

        $response->assertSuccessful();
        $response->assertSee('Sisa Saldo Berobat');
        $response->assertSee('15,000,000'); // remaining = 20jt - 5jt, matches this view's existing number_format() convention
    }

    public function test_reset_command_carries_forward_the_previous_years_allocation_not_the_config_default(): void
    {
        Employee::factory()->create(['job_position' => 'Teknisi', 'is_active' => true])
            ->healthBalances()->create(['year' => 2025, 'allocated_amount' => 15000000, 'used_amount' => 5000000]);

        config(['hr.default_health_allocation' => 20000000]);

        $this->artisan('claims:reset-health-balances', ['--year' => 2026])->assertExitCode(0);

        $this->assertDatabaseHas('employee_health_balances', ['year' => 2026, 'allocated_amount' => 15000000, 'used_amount' => 0]);
    }
}
