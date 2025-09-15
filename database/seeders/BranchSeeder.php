<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branch = new Branch;
        $branch->name = 'Acegard Pusat';
        $branch->city = 'Tangerang';
        $branch->address = 'Jl. KH. Hasyim Ashari, RT.007/RW.002, Nerogtog, Kec. Cipondoh, Kota Tangerang, Banten 15146';
        $branch->save();

        $branch = new Branch;
        $branch->name = 'Acegard Bandung';
        $branch->city = 'Bandung';
        $branch->address = 'Jl. KH. Hasyim Ashari, RT.007/RW.002, Nerogtog, Kec. Cipondoh, Kota Tangerang, Banten 15146';
        $branch->save();

        $branch = new Branch;
        $branch->name = 'Acegard Jambi';
        $branch->city = 'Jambi';
        $branch->address = 'Jl. KH. Hasyim Ashari, RT.007/RW.002, Nerogtog, Kec. Cipondoh, Kota Tangerang, Banten 15146';
        $branch->save();

        $branch = new Branch;
        $branch->name = 'Acegard Bengkulu';
        $branch->city = 'Bengkulu';
        $branch->address = 'Jl. KH. Hasyim Ashari, RT.007/RW.002, Nerogtog, Kec. Cipondoh, Kota Tangerang, Banten 15146';
        $branch->save();
    }
}
