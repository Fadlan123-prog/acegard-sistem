<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ── Permissions ───────────────────────────────────────────────
        $permissionList = [
            'release_commission',    // rilis komisi dari invoice
            'manage_commission',     // pindah / edit / hapus komisi
            'view_all_commissions',  // lihat semua komisi (tidak hanya milik sendiri)
            'view_all_customers',    // lihat semua customer (tidak hanya milik sendiri)
            'manage_employees',      // CRUD karyawan
            'manage_branches',       // CRUD cabang
            'manage_users',          // CRUD user & role
            'view_reports',          // laporan keuangan & analitik
            'manage_film_stock',     // tambah / edit stok film
            'manage_prices',         // ubah harga / konfigurasi komisi
            'manage_finance',        // kas kecil, bank, rekonsiliasi
            'manage_hutang',         // hutang / piutang CRUD
            'manage_purchases',      // pembelian CRUD
            'manage_payroll',        // payroll & absensi
        ];

        foreach ($permissionList as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ── Roles ─────────────────────────────────────────────────────
        $superadmin       = Role::firstOrCreate(['name' => 'superadmin']);
        $owner            = Role::firstOrCreate(['name' => 'owner']);
        $admin            = Role::firstOrCreate(['name' => 'admin']);
        $mgrOps           = Role::firstOrCreate(['name' => 'manager_operasional']);
        $mgrMarketing     = Role::firstOrCreate(['name' => 'manager_marketing']);
        $marketing        = Role::firstOrCreate(['name' => 'marketing']);
        $finance          = Role::firstOrCreate(['name' => 'finance']);
        $purchasing       = Role::firstOrCreate(['name' => 'purchasing']);
        $customerService  = Role::firstOrCreate(['name' => 'customer_service']);
        Role::firstOrCreate(['name' => 'teknisi']);
        Role::firstOrCreate(['name' => 'kenek']);

        // ── Role → permission matrix ──────────────────────────────────
        $superadmin->syncPermissions($permissionList);

        $owner->syncPermissions([
            'release_commission', 'manage_commission', 'view_all_commissions',
            'view_all_customers', 'view_reports', 'manage_finance',
            'manage_hutang', 'manage_payroll',
        ]);

        $admin->syncPermissions([
            'release_commission', 'manage_commission', 'view_all_commissions',
            'view_all_customers', 'manage_employees', 'manage_branches',
            'manage_users', 'view_reports', 'manage_film_stock',
            'manage_finance', 'manage_hutang', 'manage_purchases', 'manage_payroll',
        ]);

        $mgrOps->syncPermissions([
            'release_commission', 'manage_commission', 'view_all_commissions',
            'view_all_customers', 'manage_film_stock', 'view_reports',
        ]);

        $mgrMarketing->syncPermissions([
            'view_all_customers', 'view_reports',
        ]);

        $marketing->syncPermissions([]);   // data scoping via controller, no extra perms

        $finance->syncPermissions([
            'view_all_commissions', 'manage_finance', 'manage_hutang',
            'view_reports',
        ]);

        $purchasing->syncPermissions([
            'manage_purchases', 'manage_film_stock',
        ]);

        $customerService->syncPermissions([
            'view_all_customers',
        ]);

        // ── Fixed users (superadmin + existing admin) ─────────────────
        $superUser = User::firstOrCreate(
            ['email' => 'acegard@acegard.id'],
            ['name' => 'Acegard Admin', 'password' => Hash::make('Acegard.21')]
        );
        $superUser->syncRoles([$superadmin]);

        $adminUser = User::firstOrCreate(
            ['email' => 'acegard.jambi@acegard.id'],
            ['name' => 'Admin Cabang Jambi', 'password' => Hash::make('Acegard.Jambi21')]
        );
        $adminUser->syncRoles([$admin]);

        // ── Demo users per role — only when at least one branch exists ─
        $defaultBranch = Branch::orderBy('id')->first();

        if ($defaultBranch) {
            $demoUsers = [
                ['email' => 'owner@acegard.id',      'name' => 'Demo Owner',             'role' => 'owner'],
                ['email' => 'mgrops@acegard.id',     'name' => 'Demo Manajer Operasional','role' => 'manager_operasional'],
                ['email' => 'mgrmkt@acegard.id',     'name' => 'Demo Manajer Marketing', 'role' => 'manager_marketing'],
                ['email' => 'marketing@acegard.id',  'name' => 'Demo Marketing',         'role' => 'marketing'],
                ['email' => 'finance@acegard.id',    'name' => 'Demo Finance',           'role' => 'finance'],
                ['email' => 'purchasing@acegard.id', 'name' => 'Demo Purchasing',        'role' => 'purchasing'],
                ['email' => 'cs@acegard.id',         'name' => 'Demo Customer Service',  'role' => 'customer_service'],
            ];

            foreach ($demoUsers as $data) {
                $u = User::firstOrCreate(
                    ['email' => $data['email']],
                    ['name' => $data['name'], 'password' => Hash::make('Acegard.21')]
                );
                $u->syncRoles([$data['role']]);
                // Assign to default branch without removing any existing branch assignments
                $u->branches()->syncWithoutDetaching([$defaultBranch->id]);
            }
        }
    }
}
