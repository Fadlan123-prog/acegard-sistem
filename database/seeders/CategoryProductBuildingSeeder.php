<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CategoryProductBuilding;

class CategoryProductBuildingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $category = new CategoryProductBuilding();
        $category->name = '4K ALPHA PRO';
        $category->duration = 7;
        $category->save();

        $category = new CategoryProductBuilding();
        $category->name = 'NOTCH UV 400';
        $category->duration = 5;
        $category->save();

        $category = new CategoryProductBuilding();
        $category->name = 'BLACK MASTER';
        $category->duration = 5;
        $category->save();

        $category = new CategoryProductBuilding();
        $category->name = 'FLEX ONE WAY';
        $category->duration = 5;
        $category->save();
    }
}
