<?php

namespace Tests\Feature\Security;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Admin terikat cabang: tidak boleh mengelola superadmin/owner, user cabang
 * lain, menugaskan ke cabang lain, atau menambah/menghapus cabang.
 */
class AccountScopeTest extends TestCase
{
    use RefreshDatabase;

    private Branch $a;
    private Branch $b;

    protected function setUp(): void
    {
        parent::setUp();
        $this->a = Branch::factory()->create();
        $this->b = Branch::factory()->create();
    }

    private function payload(User $u, array $over = []): array
    {
        return array_merge([
            'name' => $u->name, 'email' => $u->email,
            'role' => 'finance', 'branch_ids' => [$this->a->id],
        ], $over);
    }

    public function test_admin_cannot_edit_superadmin_or_owner(): void
    {
        $admin = $this->userWithRole('admin', $this->a);
        $super = $this->userWithRole('superadmin', $this->a);
        $owner = $this->userWithRole('owner', $this->a);

        foreach ([$super, $owner] as $target) {
            $this->actingAs($admin)->get(route('users.edit', $target->id))->assertForbidden();
            $this->actingAs($admin)->delete(route('users.destroy', $target->id))->assertForbidden();
            $this->actingAs($admin)->put(route('users.update', $target->id),
                $this->payload($target, ['password' => 'Baru-Sekali-2026', 'password_confirmation' => 'Baru-Sekali-2026'])
            )->assertForbidden();
        }
    }

    public function test_admin_cannot_manage_user_of_other_branch(): void
    {
        $admin = $this->userWithRole('admin', $this->a);
        $other = $this->userWithRole('finance', $this->b);

        $this->actingAs($admin)->get(route('users.edit', $other->id))->assertForbidden();
        $this->actingAs($admin)->delete(route('users.destroy', $other->id))->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $other->id]);
    }

    public function test_admin_cannot_assign_foreign_branch_or_owner_role(): void
    {
        $admin = $this->userWithRole('admin', $this->a);
        $mate = $this->userWithRole('finance', $this->a);

        $this->actingAs($admin)->put(route('users.update', $mate->id),
            $this->payload($mate, ['branch_ids' => [$this->b->id]])
        )->assertSessionHasErrors('branch_ids.0');

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $this->actingAs($admin)->put(route('users.update', $mate->id),
            $this->payload($mate, ['role' => 'owner'])
        )->assertForbidden();

        $this->assertEquals([$this->a->id], $mate->fresh()->accessibleBranchIds());
    }

    public function test_admin_can_still_manage_same_branch_user_and_password_change_revokes_sessions(): void
    {
        config(['session.driver' => 'database']);
        $admin = $this->userWithRole('admin', $this->a);
        $mate = $this->userWithRole('finance', $this->a);

        DB::table('sessions')->insert([
            'id' => 'victim-session', 'user_id' => $mate->id, 'ip_address' => '1.1.1.1',
            'user_agent' => 'x', 'payload' => '', 'last_activity' => time(),
        ]);

        $this->actingAs($admin)->put(route('users.update', $mate->id),
            $this->payload($mate, ['password' => 'Baru-Sekali-2026', 'password_confirmation' => 'Baru-Sekali-2026'])
        )->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('sessions', ['id' => 'victim-session']);
    }

    public function test_admin_cannot_create_delete_or_edit_other_branches(): void
    {
        $admin = $this->userWithRole('admin', $this->a);

        $this->actingAs($admin)->get(route('branches.create'))->assertForbidden();
        $this->actingAs($admin)->delete(route('branches.destroy', $this->a->id))->assertForbidden();
        $this->actingAs($admin)->get(route('branches.edit', $this->b->id))->assertForbidden();
        $this->actingAs($admin)->get(route('branches.edit', $this->a->id))->assertOk();
    }

    public function test_superadmin_still_manages_everything(): void
    {
        $super = $this->userWithRole('superadmin', $this->a);
        $owner = $this->userWithRole('owner', $this->b);

        $this->actingAs($super)->get(route('users.edit', $owner->id))->assertOk();
        $this->actingAs($super)->get(route('branches.create'))->assertOk();
    }
}
