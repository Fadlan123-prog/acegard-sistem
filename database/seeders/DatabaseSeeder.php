<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Safe to run against production: reference data only, no user accounts.
        $this->call([
            RoleSeeder::class,
            CategoryProductSeeder::class,
            ProductSeeder::class,
            PartSeeder::class,
            BranchSeeder::class,
            CategoryProductBuildingSeeder::class,
            ProductBuildingSeeder::class,
            EmployeeSeeder::class,
            PpfProductSeeder::class,
            ApprovalFlowSeeder::class,
        ]);

        // Fixed-credential demo/dev accounts — local development only.
        // Production's first superadmin account must be created via
        // `php artisan app:create-superadmin` instead.
        if (app()->environment('local')) {
            $this->call([
                TeknisiSeeder::class,
                DevAccountSeeder::class,
                DemoDataSeeder::class,
            ]);
        }
    }
}
