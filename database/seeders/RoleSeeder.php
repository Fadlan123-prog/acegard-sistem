<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
            'manage_sales_network',  // approve rekrutan sales baru, kelola produk sales
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
        $sales            = Role::firstOrCreate(['name' => 'sales']);
        $finance          = Role::firstOrCreate(['name' => 'finance']);
        $purchasing       = Role::firstOrCreate(['name' => 'purchasing']);
        $customerService  = Role::firstOrCreate(['name' => 'customer_service']);
        Role::firstOrCreate(['name' => 'teknisi']);
        Role::firstOrCreate(['name' => 'kenek']);

        // ── Restrukturisasi ERP (2026-08) ──────────────────────────────
        $itManager            = Role::firstOrCreate(['name' => 'it_manager']);
        $development          = Role::firstOrCreate(['name' => 'development']);
        $exportImport         = Role::firstOrCreate(['name' => 'export_import']);
        $strategiPengembangan = Role::firstOrCreate(['name' => 'strategi_pengembangan']);
        Role::firstOrCreate(['name' => 'designer']);
        Role::firstOrCreate(['name' => 'videographer']);

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
            'manage_sales_network',
        ]);

        $mgrOps->syncPermissions([
            'release_commission', 'manage_commission', 'view_all_commissions',
            'view_all_customers', 'manage_film_stock', 'view_reports',
        ]);

        $mgrMarketing->syncPermissions([
            'view_all_customers', 'view_reports',
        ]);

        $marketing->syncPermissions([]);   // data scoping via controller, no extra perms
        $sales->syncPermissions([]);       // data scoping via controller, no extra perms

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

        // IT Manager saat ini merangkap Purchasing & Finance sekaligus (satu
        // role gabungan, bukan satu akun dengan banyak role — lihat analisis
        // restrukturisasi ERP). Permission-nya union dari kedua role itu.
        $itManager->syncPermissions([
            'manage_purchases', 'manage_film_stock',
            'view_all_commissions', 'manage_finance', 'manage_hutang', 'view_reports',
        ]);

        // Development, Export & Import, Strategi Pengembangan, Designer,
        // Videographer: tidak butuh permission granular tambahan — akses
        // dibatasi lewat role middleware per route/controller, sama seperti
        // pola role 'marketing' di atas.
        $development->syncPermissions([]);
        $exportImport->syncPermissions([]);
        $strategiPengembangan->syncPermissions([]);

        // Roles & permissions only — never create user accounts here.
        // Fixed-credential accounts must not exist in any seeder that can
        // reach a production database. See DevAccountSeeder (local/testing
        // only) and `php artisan app:create-superadmin` for production.
    }
}
