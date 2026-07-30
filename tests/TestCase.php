<?php

namespace Tests;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create a user with the given role, assigned to $branch, with that branch
     * set as their active session branch — matching what EnsureBranchAccess
     * guarantees in production (see routes/web.php's branch.access middleware).
     */
    protected function userWithRole(string $role, ?Branch $branch = null): User
    {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($role);

        if ($branch) {
            $user->branches()->attach($branch->id);
            session(['active_branch_id' => $branch->id]);
        }

        return $user;
    }

    protected function superadmin(): User
    {
        return $this->userWithRole('superadmin');
    }
}
