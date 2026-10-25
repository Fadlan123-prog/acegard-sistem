<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Fixed-credential demo accounts for local development only.
 *
 * DatabaseSeeder only calls this seeder when app()->environment('local'),
 * and it additionally refuses to run outside 'local' as a second line of
 * defense — this must never provision accounts on a production database.
 */
class DevAccountSeeder extends Seeder
{
    /**
     * Fixed, known password for every account this seeder creates. Fine to
     * hardcode — this class already refuses to run outside `local`, and a
     * guessable local-only password is exactly what manual QA needs to be
     * able to log in as every role from the Inspection Lane checklist.
     */
    private const PASSWORD = 'Acegard.12';

    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->error('DevAccountSeeder: refusing to run outside the local environment.');
            return;
        }

        $credentials = [];

        $superUser = User::firstOrCreate(
            ['email' => 'acegard@acegard.id'],
            ['name' => 'Acegard Admin', 'password' => Hash::make(self::PASSWORD)]
        );
        $superUser->update(['password' => Hash::make(self::PASSWORD)]);
        $superUser->syncRoles(['superadmin']);
        $credentials[] = ['acegard@acegard.id', 'superadmin', '(semua cabang)'];

        $branches = Branch::orderBy('id')->get()->values();
        $pusat    = $branches->get(0);
        $bandung  = $branches->get(1);
        $jambi    = $branches->get(2);

        // Branch-scoped admins — three distinct branches so manual QA can
        // actually exercise cross-branch 403s and "Switch Branch" (section C).
        $branchAdmins = [
            ['email' => 'acegard.jambi@acegard.id',   'name' => 'Admin Cabang Jambi',   'branch' => $jambi],
            ['email' => 'acegard.bandung@acegard.id', 'name' => 'Admin Cabang Bandung', 'branch' => $bandung],
        ];

        foreach ($branchAdmins as $data) {
            $u = User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => Hash::make(self::PASSWORD)]
            );
            $u->update(['password' => Hash::make(self::PASSWORD)]);
            $u->syncRoles(['admin']);
            if ($data['branch']) {
                $u->branches()->syncWithoutDetaching([$data['branch']->id]);
            }
            $credentials[] = [$data['email'], 'admin', $data['branch']?->name ?? '-'];
        }

        if ($pusat) {
            $demoUsers = [
                ['email' => 'owner@acegard.id',      'name' => 'Demo Owner',              'role' => 'owner'],
                ['email' => 'mgrops@acegard.id',     'name' => 'Demo Manajer Operasional','role' => 'manager_operasional'],
                ['email' => 'mgrmkt@acegard.id',     'name' => 'Demo Manajer Marketing',  'role' => 'manager_marketing'],
                ['email' => 'marketing@acegard.id',  'name' => 'Demo Marketing',          'role' => 'marketing'],
                ['email' => 'finance@acegard.id',    'name' => 'Demo Finance',            'role' => 'finance'],
                ['email' => 'purchasing@acegard.id', 'name' => 'Demo Purchasing',         'role' => 'purchasing'],
                ['email' => 'cs@acegard.id',         'name' => 'Demo Customer Service',   'role' => 'customer_service'],
                // Restrukturisasi ERP (2026-08) — IT Manager inherits Purchasing+Finance's per-branch scoping.
                ['email' => 'itmgr@acegard.id',      'name' => 'Demo IT Manager',         'role' => 'it_manager'],
            ];

            foreach ($demoUsers as $data) {
                $u = User::firstOrCreate(
                    ['email' => $data['email']],
                    ['name' => $data['name'], 'password' => Hash::make(self::PASSWORD)]
                );
                $u->update(['password' => Hash::make(self::PASSWORD)]);
                $u->syncRoles([$data['role']]);
                $u->branches()->syncWithoutDetaching([$pusat->id]);
                $credentials[] = [$data['email'], $data['role'], $pusat->name];
            }
        }

        // Restrukturisasi ERP (2026-08) — company-wide roles, no branch assignment needed.
        $noBranchDemoUsers = [
            ['email' => 'development@acegard.id', 'name' => 'Demo Development',           'role' => 'development'],
            ['email' => 'exportimport@acegard.id','name' => 'Demo Export Import',         'role' => 'export_import'],
            ['email' => 'strategi@acegard.id',    'name' => 'Demo Strategi Pengembangan', 'role' => 'strategi_pengembangan'],
            ['email' => 'designer@acegard.id',    'name' => 'Demo Designer',              'role' => 'designer'],
            ['email' => 'videografer@acegard.id', 'name' => 'Demo Videographer',          'role' => 'videographer'],
        ];

        foreach ($noBranchDemoUsers as $data) {
            $u = User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => Hash::make(self::PASSWORD)]
            );
            $u->update(['password' => Hash::make(self::PASSWORD)]);
            $u->syncRoles([$data['role']]);
            $credentials[] = [$data['email'], $data['role'], '(company-wide)'];
        }

        $this->command?->info('DevAccountSeeder: accounts ready, password for all of them is "' . self::PASSWORD . '".');
        $this->command?->table(['Email', 'Role', 'Branch'], $credentials);
    }
}
