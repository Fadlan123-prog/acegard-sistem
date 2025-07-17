<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);

        // Buat user superadmin
        $user = User::firstOrCreate(
            ['email' => 'acegard@acegard.id'],
            [
                'name' => 'Acegard Admin',
                'password' => Hash::make('Acegard.21'), // Ganti dengan password aman
            ]
        );

        $user->assignRole($superadmin);

        // Buat user admin biasa
        $adminUser = User::firstOrCreate(
            ['email' => 'acegard.jambi@acegard.id'],
            [
                'name' => 'Admin Cabang Jambi',
                'password' => Hash::make('Acegard.Jambi21'),
            ]
        );

        $adminUser->assignRole($admin);
    }
}
