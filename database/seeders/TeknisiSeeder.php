<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeknisiSeeder extends Seeder
{
    /**
     * Seed demo teknisi & kenek accounts.
     *
     * Each entry creates (or updates) an Employee record and a linked User
     * account with the correct role.  Safe to run multiple times — uses
     * firstOrCreate so existing records are not duplicated.
     */
    public function run(): void
    {
        $defaultBranch = Branch::orderBy('id')->first();

        $accounts = [
            // Teknisi
            [
                'employee' => [
                    'name'         => 'Rizki',
                    'job_position' => 'Teknisi',
                    'phone_number' => '081200000001',
                    'is_active'    => true,
                ],
                'user' => [
                    'name'     => 'Rizki',
                    'email'    => 'rizki.teknisi@acegard.id',
                    'password' => 'Acegard.21',
                ],
                'role' => 'teknisi',
            ],
            [
                'employee' => [
                    'name'         => 'Dhani',
                    'job_position' => 'Teknisi',
                    'phone_number' => '081200000002',
                    'is_active'    => true,
                ],
                'user' => [
                    'name'     => 'Dhani',
                    'email'    => 'dhani.teknisi@acegard.id',
                    'password' => 'Acegard.21',
                ],
                'role' => 'teknisi',
            ],
            [
                'employee' => [
                    'name'         => 'Ferdi',
                    'job_position' => 'Teknisi',
                    'phone_number' => '081200000003',
                    'is_active'    => true,
                ],
                'user' => [
                    'name'     => 'Ferdi',
                    'email'    => 'ferdi.teknisi@acegard.id',
                    'password' => 'Acegard.21',
                ],
                'role' => 'teknisi',
            ],
            [
                'employee' => [
                    'name'         => 'Agil',
                    'job_position' => 'Teknisi',
                    'phone_number' => '081200000004',
                    'is_active'    => true,
                ],
                'user' => [
                    'name'     => 'Agil',
                    'email'    => 'agil.teknisi@acegard.id',
                    'password' => 'Acegard.21',
                ],
                'role' => 'teknisi',
            ],
            [
                'employee' => [
                    'name'         => 'Asep',
                    'job_position' => 'Teknisi',
                    'phone_number' => '081200000005',
                    'is_active'    => true,
                ],
                'user' => [
                    'name'     => 'Asep',
                    'email'    => 'asep.teknisi@acegard.id',
                    'password' => 'Acegard.21',
                ],
                'role' => 'teknisi',
            ],
            // Demo teknisi (mudah diingat untuk testing)
            [
                'employee' => [
                    'name'         => 'Demo Teknisi',
                    'job_position' => 'Teknisi',
                    'phone_number' => '081200000099',
                    'is_active'    => true,
                ],
                'user' => [
                    'name'     => 'Demo Teknisi',
                    'email'    => 'teknisi@acegard.id',
                    'password' => 'Acegard.21',
                ],
                'role' => 'teknisi',
            ],
            // Kenek
            [
                'employee' => [
                    'name'         => 'Fajri',
                    'job_position' => 'Kenek',
                    'phone_number' => '081200000011',
                    'is_active'    => true,
                ],
                'user' => [
                    'name'     => 'Fajri',
                    'email'    => 'fajri.kenek@acegard.id',
                    'password' => 'Acegard.21',
                ],
                'role' => 'kenek',
            ],
            [
                'employee' => [
                    'name'         => 'Alam',
                    'job_position' => 'Kenek',
                    'phone_number' => '081200000012',
                    'is_active'    => true,
                ],
                'user' => [
                    'name'     => 'Alam',
                    'email'    => 'alam.kenek@acegard.id',
                    'password' => 'Acegard.21',
                ],
                'role' => 'kenek',
            ],
            // Demo kenek
            [
                'employee' => [
                    'name'         => 'Demo Kenek',
                    'job_position' => 'Kenek',
                    'phone_number' => '081200000098',
                    'is_active'    => true,
                ],
                'user' => [
                    'name'     => 'Demo Kenek',
                    'email'    => 'kenek@acegard.id',
                    'password' => 'Acegard.21',
                ],
                'role' => 'kenek',
            ],
        ];

        foreach ($accounts as $data) {
            // 1. Cari atau buat Employee
            $employeeData = $data['employee'];
            if ($defaultBranch) {
                $employeeData['branch_id'] = $defaultBranch->id;
            }

            $employee = Employee::firstOrCreate(
                ['name' => $employeeData['name'], 'job_position' => $employeeData['job_position']],
                $employeeData
            );

            // 2. Cari atau buat User dan link ke Employee
            $user = User::firstOrCreate(
                ['email' => $data['user']['email']],
                [
                    'name'        => $data['user']['name'],
                    'password'    => Hash::make($data['user']['password']),
                    'employee_id' => $employee->id,
                ]
            );

            // Pastikan employee_id ter-set meski user sudah ada
            if (! $user->employee_id) {
                $user->update(['employee_id' => $employee->id]);
            }

            // 3. Assign role
            $user->syncRoles([$data['role']]);

            // 4. Assign ke branch default jika ada
            if ($defaultBranch) {
                $user->branches()->syncWithoutDetaching([$defaultBranch->id]);
            }
        }

        $this->command->info('TeknisiSeeder: ' . count($accounts) . ' akun berhasil dibuat/diperbarui.');
        $this->command->table(
            ['Email', 'Role', 'Password'],
            collect($accounts)->map(fn($a) => [
                $a['user']['email'],
                $a['role'],
                $a['user']['password'],
            ])->toArray()
        );
    }
}
