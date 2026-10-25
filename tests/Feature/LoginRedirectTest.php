<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * LoginController has its own role whitelist and per-role redirect/branch
 * logic, entirely separate from UserController's and EnsureBranchAccess's
 * role lists. Nothing previously exercised POST /login end-to-end, which is
 * how the 2026-08 ERP restructuring roles (it_manager, development,
 * export_import, strategi_pengembangan, designer, videographer) slipped
 * through with valid accounts that still couldn't log in.
 */
class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function login(string $email, string $password = 'secret123')
    {
        return $this->post(route('login'), ['email' => $email, 'password' => $password]);
    }

    public function test_it_manager_logs_in_and_lands_on_purchasing_dashboard(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $branch = Branch::factory()->create();
        $user = $this->userWithRole('it_manager', $branch);
        $user->update(['password' => bcrypt('secret123')]);

        $this->login($user->email)->assertRedirect(route('purchasing.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_it_manager_without_a_branch_is_rejected_with_branch_error(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $user = $this->userWithRole('it_manager');
        $user->update(['password' => bcrypt('secret123')]);

        $this->login($user->email)->assertSessionHasErrors('branch');
        $this->assertGuest();
    }

    public function test_development_role_logs_in_without_branch_and_lands_on_reports(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $user = $this->userWithRole('development');
        $user->update(['password' => bcrypt('secret123')]);

        $this->login($user->email)->assertRedirect(route('development-reports.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_export_import_role_logs_in_without_branch_and_lands_on_import_orders(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $user = $this->userWithRole('export_import');
        $user->update(['password' => bcrypt('secret123')]);

        $this->login($user->email)->assertRedirect(route('import-orders.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_strategi_pengembangan_designer_and_videographer_land_on_creative_tasks(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        foreach (['strategi_pengembangan', 'designer', 'videographer'] as $role) {
            $user = $this->userWithRole($role);
            $user->update(['password' => bcrypt('secret123')]);

            $this->login($user->email)->assertRedirect(route('creative-tasks.index'));
            $this->assertAuthenticatedAs($user);
            $this->post(route('logout'));
        }
    }

    public function test_role_without_login_access_is_rejected(): void
    {
        \Spatie\Permission\Models\Role::create(['name' => 'unlisted_role', 'guard_name' => 'web']);
        $user = User::factory()->create(['password' => bcrypt('secret123')]);
        $user->assignRole('unlisted_role');

        $this->login($user->email)->assertSessionHasErrors('role');
        $this->assertGuest();
    }

    public function test_superadmin_logs_in_and_lands_on_dashboard(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        Branch::factory()->create(['name' => 'Kantor Pusat']);
        $user = $this->userWithRole('superadmin');
        $user->update(['password' => bcrypt('secret123')]);

        $this->login($user->email)->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
