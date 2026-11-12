<?php

namespace Tests\Feature\Security;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\SalesNetworkProfile;
use App\Models\User;
use App\Notifications\PasswordSetupLink;
use App\Services\PasswordSetupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Atur-password sales: undangan setelah approve, lupa password, dan password
 * sementara dari admin. Fokus pada hal yang bisa disalahgunakan: akun yang
 * belum disetujui, role di luar izin, tautan kedaluwarsa/dipakai ulang,
 * enumerasi akun, dan host-header poisoning.
 */
class SalesPasswordSetupTest extends TestCase
{
    use RefreshDatabase;

    private const GOOD = 'Kuat-Sekali-2026';

    private function salesUser(string $status = 'active', bool $employeeActive = true): User
    {
        Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);

        $branch = Branch::factory()->create();
        $employee = Employee::factory()->create([
            'branch_id'    => $branch->id,
            'job_position' => 'Sales',
            'is_active'    => $employeeActive,
        ]);

        $user = User::factory()->create([
            'employee_id' => $employee->id,
            'password'    => Hash::make('Password-Lama-1'),
        ]);
        $user->assignRole('sales');
        $user->branches()->attach($branch->id);

        SalesNetworkProfile::create([
            'employee_id'   => $employee->id,
            'referral_code' => 'REF-' . strtoupper(Str::random(6)),
            'status'        => $status,
        ]);

        return $user;
    }

    private function adminWithPassword(string $password = 'Admin-Pass-2026'): User
    {
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $admin->update(['password' => Hash::make($password)]);
        // Admin terikat cabang: beri akses ke cabang sales yang sudah dibuat test.
        $admin->branches()->syncWithoutDetaching(Branch::pluck('id')->all());

        return $admin;
    }

    private function resetPost(User $user, string $token, string $password = self::GOOD)
    {
        return $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => $password,
            'password_confirmation' => $password,
        ]);
    }

    // ───────────────────────── lupa password ─────────────────────────

    public function test_active_sales_receives_a_reset_link(): void
    {
        Notification::fake();
        $user = $this->salesUser();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, PasswordSetupLink::class);
    }

    public function test_response_is_identical_and_nothing_is_sent_for_ineligible_accounts(): void
    {
        Notification::fake();
        $pending = $this->salesUser('pending_approval');
        $deactivated = $this->salesUser('active', employeeActive: false);
        $admin = $this->userWithRole('admin', Branch::factory()->create());

        $expected = $this->post(route('password.email'), ['email' => $this->salesUser()->email])
            ->getSession()->get('status');

        foreach ([$pending->email, $deactivated->email, $admin->email, 'tidak-ada@contoh.test'] as $email) {
            $this->post(route('password.email'), ['email' => $email])
                ->assertSessionHas('status', $expected)
                ->assertSessionHasNoErrors();
        }

        Notification::assertNotSentTo($pending, PasswordSetupLink::class);
        Notification::assertNotSentTo($deactivated, PasswordSetupLink::class);
        Notification::assertNotSentTo($admin, PasswordSetupLink::class);
    }

    public function test_account_with_an_extra_privileged_role_cannot_use_email_reset(): void
    {
        Notification::fake();
        $user = $this->salesUser();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user->assignRole('admin');

        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertNotSentTo($user, PasswordSetupLink::class);
    }

    public function test_link_is_built_from_app_url_not_from_the_host_header(): void
    {
        Notification::fake();
        config(['app.url' => 'https://erp.acegard.test']);
        $user = $this->salesUser();

        $this->withServerVariables(['HTTP_HOST' => 'evil.example'])
            ->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, PasswordSetupLink::class, function ($notification) use ($user) {
            $url = $notification->toMail($user)->actionUrl;

            return str_starts_with($url, 'https://erp.acegard.test/atur-password/')
                && ! str_contains($url, 'evil.example');
        });
    }

    public function test_forgot_password_is_rate_limited_per_email(): void
    {
        Notification::fake();
        $user = $this->salesUser();

        foreach (range(1, 3) as $_) {
            $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');
        }

        $this->post(route('password.email'), ['email' => $user->email])->assertStatus(429);
    }

    // ───────────────────────── penukaran token ─────────────────────────

    public function test_valid_reset_token_sets_the_password_once(): void
    {
        $user = $this->salesUser();
        $token = Password::broker('users')->createToken($user);

        $this->resetPost($user, $token)->assertRedirect(route('login'));

        $user->refresh();
        $this->assertTrue(Hash::check(self::GOOD, $user->password));
        $this->assertNotNull($user->password_changed_at);
        $this->assertFalse($user->must_change_password);

        // Dipakai ulang → ditolak, password tidak berubah lagi.
        $this->resetPost($user, $token, 'Lain-Lagi-9999')->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check(self::GOOD, $user->fresh()->password));
    }

    public function test_weak_passwords_are_rejected_and_token_stays_usable(): void
    {
        $user = $this->salesUser();
        $token = Password::broker('users')->createToken($user);

        // 4 percobaan + 1 yang benar = 5, tepat di batas limiter 5/menit.
        foreach (['pendek1A', 'semuahurufkecil123', 'TanpaAngkaSamaSekali', $user->email] as $weak) {
            $this->resetPost($user, $token, $weak)->assertSessionHasErrors('password');
        }

        $this->assertTrue(Hash::check('Password-Lama-1', $user->fresh()->password));
        $this->resetPost($user, $token)->assertRedirect(route('login'));
    }

    public function test_reset_endpoint_is_rate_limited_per_email(): void
    {
        $user = $this->salesUser();

        foreach (range(1, 5) as $_) {
            $this->resetPost($user, 'token-tebakan')->assertSessionHasErrors('email');
        }

        $token = Password::broker('users')->createToken($user);
        $this->resetPost($user, $token)->assertStatus(429);
        $this->assertTrue(Hash::check('Password-Lama-1', $user->fresh()->password));
    }

    public function test_reset_token_expires_after_60_minutes(): void
    {
        $user = $this->salesUser();
        $token = Password::broker('users')->createToken($user);

        $this->travel(61)->minutes();

        $this->resetPost($user, $token)->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('Password-Lama-1', $user->fresh()->password));
    }

    public function test_invitation_token_lasts_48_hours_but_not_longer(): void
    {
        $user = $this->salesUser();
        $token = Password::broker('sales_invitations')->createToken($user);

        $this->travel(47)->hours();
        $this->resetPost($user, $token)->assertRedirect(route('login'));

        $other = $this->salesUser();
        $expired = Password::broker('sales_invitations')->createToken($other);
        $this->travel(49)->hours();
        $this->resetPost($other, $expired)->assertSessionHasErrors('email');
    }

    public function test_a_wrong_token_is_rejected(): void
    {
        $user = $this->salesUser();
        Password::broker('users')->createToken($user);

        $this->resetPost($user, 'token-asal-asalan')->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('Password-Lama-1', $user->fresh()->password));
    }

    public function test_token_cannot_be_used_after_the_account_loses_eligibility(): void
    {
        $user = $this->salesUser();
        $token = Password::broker('users')->createToken($user);

        $user->employee->update(['is_active' => false]);

        $this->resetPost($user, $token)->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('Password-Lama-1', $user->fresh()->password));
    }

    public function test_a_valid_token_does_not_work_for_a_privileged_role(): void
    {
        $admin = $this->userWithRole('admin', Branch::factory()->create());
        $token = Password::broker('users')->createToken($admin);
        $before = $admin->fresh()->password;

        $this->resetPost($admin, $token)->assertSessionHasErrors('email');
        $this->assertSame($before, $admin->fresh()->password);
    }

    public function test_reset_cuts_existing_sessions_and_remember_token(): void
    {
        config(['session.driver' => 'database']);
        $user = $this->salesUser();
        $oldRemember = $user->remember_token;
        DB::table('sessions')->insert([
            'id' => 'sesi-lama', 'user_id' => $user->id, 'payload' => 'x', 'last_activity' => time(),
        ]);

        app(PasswordSetupService::class)->applyNewPassword($user, self::GOOD);

        $this->assertDatabaseMissing('sessions', ['id' => 'sesi-lama']);
        $this->assertNotSame($oldRemember, $user->fresh()->remember_token);
    }

    // ───────────────────────── gerbang login ─────────────────────────

    public function test_pending_sales_cannot_log_in_even_with_the_right_password(): void
    {
        $user = $this->salesUser('pending_approval');

        $this->post(route('login'), ['email' => $user->email, 'password' => 'Password-Lama-1'])
            ->assertSessionHasErrors('account');
        $this->assertGuest();
    }

    public function test_active_sales_can_log_in(): void
    {
        $user = $this->salesUser();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'Password-Lama-1'])
            ->assertRedirect(route('sales.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    // ───────────────────────── approve ─────────────────────────

    public function test_approve_with_invitation_activates_and_sends_a_link_without_touching_the_password(): void
    {
        Notification::fake();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $pending = $this->salesUser('pending_approval', employeeActive: false);
        $oldHash = $pending->password;
        $admin = $this->adminWithPassword();

        $this->actingAs($admin)
            ->post(route('sales-network.approve', $pending->employee->salesNetworkProfile), ['password_mode' => 'invite'])
            ->assertSessionHas('success');

        $this->assertTrue($pending->employee->fresh()->is_active);
        $this->assertSame('active', $pending->employee->salesNetworkProfile->fresh()->status);
        $this->assertSame($oldHash, $pending->fresh()->password);
        $this->assertFalse($pending->fresh()->must_change_password);
        Notification::assertSentTo($pending, PasswordSetupLink::class);
    }

    public function test_manual_approve_requires_the_admins_own_password(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $pending = $this->salesUser('pending_approval', employeeActive: false);
        $admin = $this->adminWithPassword('Admin-Pass-2026');

        $this->actingAs($admin)
            ->post(route('sales-network.approve', $pending->employee->salesNetworkProfile), [
                'password_mode'         => 'manual',
                'password'              => self::GOOD,
                'password_confirmation' => self::GOOD,
                'current_password'      => 'bukan-password-admin',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertSame('pending_approval', $pending->employee->salesNetworkProfile->fresh()->status);
        $this->assertFalse($pending->employee->fresh()->is_active);
        $this->assertTrue(Hash::check('Password-Lama-1', $pending->fresh()->password));
    }

    public function test_manual_approve_rejects_a_weak_or_mismatched_password(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $pending = $this->salesUser('pending_approval', employeeActive: false);
        $admin = $this->adminWithPassword('Admin-Pass-2026');
        $url = route('sales-network.approve', $pending->employee->salesNetworkProfile);

        $this->actingAs($admin)->post($url, [
            'password_mode' => 'manual', 'password' => 'lemah', 'password_confirmation' => 'lemah',
            'current_password' => 'Admin-Pass-2026',
        ])->assertSessionHasErrors('password');

        $this->actingAs($admin)->post($url, [
            'password_mode' => 'manual', 'password' => self::GOOD, 'password_confirmation' => 'Beda-Sekali-2026',
            'current_password' => 'Admin-Pass-2026',
        ])->assertSessionHasErrors('password');

        $this->assertSame('pending_approval', $pending->employee->salesNetworkProfile->fresh()->status);
    }

    public function test_manual_password_forces_a_change_before_anything_else(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $pending = $this->salesUser('pending_approval', employeeActive: false);
        $admin = $this->adminWithPassword('Admin-Pass-2026');

        $this->actingAs($admin)->post(route('sales-network.approve', $pending->employee->salesNetworkProfile), [
            'password_mode' => 'manual', 'password' => 'Sementara-2026x', 'password_confirmation' => 'Sementara-2026x',
            'current_password' => 'Admin-Pass-2026',
        ])->assertSessionHasNoErrors();

        $pending->refresh();
        $this->assertTrue($pending->must_change_password);
        $this->assertTrue(Hash::check('Sementara-2026x', $pending->password));

        // Login pertama → dipaksa ke halaman ganti password; halaman lain terkunci.
        auth()->logout();
        $this->flushSession();
        $this->post(route('login'), ['email' => $pending->email, 'password' => 'Sementara-2026x'])
            ->assertRedirect(route('password.change'));
        $this->get(route('sales.dashboard'))->assertRedirect(route('password.change'));
        $this->get(route('password.change'))->assertOk();

        // Password lama salah / baru lemah → ditolak.
        $this->post(route('password.change.update'), [
            'current_password' => 'salah', 'password' => self::GOOD, 'password_confirmation' => self::GOOD,
        ])->assertSessionHasErrors('current_password');
        $this->post(route('password.change.update'), [
            'current_password' => 'Sementara-2026x', 'password' => 'Sementara-2026x', 'password_confirmation' => 'Sementara-2026x',
        ])->assertSessionHasErrors('password');

        // Berhasil → keluar, flag hilang, login normal dengan password baru.
        $this->post(route('password.change.update'), [
            'current_password' => 'Sementara-2026x', 'password' => self::GOOD, 'password_confirmation' => self::GOOD,
        ])->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertFalse($pending->fresh()->must_change_password);

        $this->post(route('login'), ['email' => $pending->email, 'password' => self::GOOD])
            ->assertRedirect(route('sales.dashboard'));
    }

    // ───────────────────────── kirim ulang undangan ─────────────────────────

    public function test_resend_invite_only_before_the_member_has_set_a_password(): void
    {
        Notification::fake();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $fresh = $this->salesUser();
        $done = $this->salesUser();
        $done->forceFill(['password_changed_at' => now()])->save();
        $admin = $this->adminWithPassword();

        $this->actingAs($admin)
            ->post(route('sales-network.resend-invite', $fresh->employee->salesNetworkProfile))
            ->assertSessionHas('success');
        Notification::assertSentTo($fresh, PasswordSetupLink::class);

        $this->actingAs($admin)
            ->post(route('sales-network.resend-invite', $done->employee->salesNetworkProfile))
            ->assertStatus(422);
        Notification::assertNotSentTo($done, PasswordSetupLink::class);
    }

    public function test_resend_invite_is_not_sent_to_a_non_sales_account(): void
    {
        Notification::fake();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $root = $this->salesUser();           // anggota jaringan, tapi role-nya admin (bukan sales)
        $root->syncRoles(['admin']);
        $admin = $this->adminWithPassword();

        $this->actingAs($admin)
            ->post(route('sales-network.resend-invite', $root->employee->salesNetworkProfile))
            ->assertSessionHas('warning');

        Notification::assertNotSentTo($root, PasswordSetupLink::class);
    }

    public function test_a_sales_user_cannot_approve_anyone(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $sales = $this->salesUser();
        $pending = $this->salesUser('pending_approval', employeeActive: false);

        $this->actingAs($sales)
            ->post(route('sales-network.approve', $pending->employee->salesNetworkProfile), ['password_mode' => 'invite'])
            ->assertForbidden();
    }
}
