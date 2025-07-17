<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Part;

class PartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $part = new Part;
        $part->name = 'WINDSHIELD';
        $part->save();

        $part = new Part;
        $part->name = 'FIRST ROW SIDE WINDOW';
        $part->save();

        $part = new Part;
        $part->name = 'SECOND ROW SIDE WINDOW';
        $part->save();

        $part = new Part;
        $part->name = 'THIRD ROW SIDE WINDOW';
        $part->save();

        $part = new Part;
        $part->name = 'REAR WINDOW';
        $part->save();

        $part = new Part;
        $part->name = 'SUNROOF/PANORAMIC';
        $part->save();
    }
}
