<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BranchUser;

class BranchUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branchUser = new BranchUser;
        $branchUser->branch_id = 1;
        $branchUser->user_id = 1;
        $branchUser->save();

        $branchUser = new BranchUser;
        $branchUser->branch_id = 2;
        $branchUser->user_id = 1;
        $branchUser->save();

        $branchUser = new BranchUser;
        $branchUser->branch_id = 2;
        $branchUser->user_id = 2;
        $branchUser->save();
    }
}
