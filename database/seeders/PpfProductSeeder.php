<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PpfProduct;

class PpfProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = new PpfProduct();
        $data->name = 'Aegis';
        $data->brand = 'Acegard';
        $data->series = 'Aegis';
        $data->thickness_microns = 1000;
        $data->finish = 'Gloss';
        $data->default_warranty_years = 1;
        $data->default_coverage = 'Full Coverage';
        $data->default_exclusions = 'None';
        $data->save();

        $data = new PpfProduct();
        $data->name = 'Aegis Max';
        $data->brand = 'Acegard';
        $data->series = 'Aegis Plus';
        $data->thickness_microns = 2000;
        $data->finish = 'Gloss';
        $data->default_warranty_years = 1;
        $data->default_coverage = 'Full Coverage';
        $data->default_exclusions = 'None';
        $data->save();

        $data = new PpfProduct();
        $data->name = 'Armor';
        $data->brand = 'Acegard';
        $data->series = 'Armor';
        $data->thickness_microns = 2000;
        $data->finish = 'Gloss';
        $data->default_warranty_years = 1;
        $data->default_coverage = 'Full Coverage';
        $data->default_exclusions = 'None';
        $data->save();

        $data = new PpfProduct();
        $data->name = 'Armor Plus';
        $data->brand = 'Acegard';
        $data->series = 'Armor Plus';
        $data->thickness_microns = 2000;
        $data->finish = 'Gloss';
        $data->default_warranty_years = 1;
        $data->default_coverage = 'Full Coverage';
        $data->default_exclusions = 'None';
        $data->save();
    }
}
