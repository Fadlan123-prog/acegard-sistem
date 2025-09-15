<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductBuilding;

class ProductBuildingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $product = new ProductBuilding();
        $product->category_product_building_id = 1;
        $product->name = 'AP50';
        $product->description = '20%';
        $product->save();

        $product = new ProductBuilding();
        $product->category_product_building_id = 1;
        $product->name = 'AP35';
        $product->description = '40%';
        $product->save();

        $product = new ProductBuilding();
        $product->category_product_building_id = 1;
        $product->name = 'AP20';
        $product->description = '60%';
        $product->save();

        $product = new ProductBuilding();
        $product->category_product_building_id = 1;
        $product->name = 'AP05';
        $product->description = '80%';
        $product->save();

        $product = new ProductBuilding();
        $product->category_product_building_id = 2;
        $product->name = 'NV70';
        $product->description = '20%';
        $product->save();

        $product = new ProductBuilding();
        $product->category_product_building_id = 2;
        $product->name = 'NV60';
        $product->description = '30%';
        $product->save();

        $product = new ProductBuilding();
        $product->category_product_building_id = 2;
        $product->name = 'NV40';
        $product->description = '40%';
        $product->save();

        $product = new ProductBuilding();
        $product->category_product_building_id = 2;
        $product->name = 'NV15';
        $product->description = '60%';
        $product->save();

        $product = new ProductBuilding();
        $product->category_product_building_id = 2;
        $product->name = 'NV05';
        $product->description = '80%';
        $product->save();

        $product = new ProductBuilding();
        $product->category_product_building_id = 3;
        $product->name = 'BM35';
        $product->description = '40%';
        $product->save();


        $product = new ProductBuilding();
        $product->category_product_building_id = 3;
        $product->name = 'BM15';
        $product->description = '60%';
        $product->save();


        $product = new ProductBuilding();
        $product->category_product_building_id = 3;
        $product->name = 'BM05';
        $product->description = '80%';
        $product->save();

        $product = new ProductBuilding();
        $product->category_product_building_id = 4;
        $product->name = 'Silver Grey';
        $product->description = '80%';
        $product->save();


        $product = new ProductBuilding();
        $product->category_product_building_id = 4;
        $product->name = 'Silver Black';
        $product->description = '80%';
        $product->save();


        $product = new ProductBuilding();
        $product->category_product_building_id = 4;
        $product->name = 'Gold Silver ';
        $product->description = '80%';
        $product->save();


        $product = new ProductBuilding();
        $product->category_product_building_id = 4;
        $product->name = 'Green Silver';
        $product->description = '80%';
        $product->save();


        $product = new ProductBuilding();
        $product->category_product_building_id = 4;
        $product->name = 'Blue Silver';
        $product->description = '80%';
        $product->save();


        $product = new ProductBuilding();
        $product->category_product_building_id = 4;
        $product->name = 'Red Silver';
        $product->description = '80%';
        $product->save();


        $product = new ProductBuilding();
        $product->category_product_building_id = 4;
        $product->name = 'Purple Silver';
        $product->description = '80%';
        $product->save();


        $product = new ProductBuilding();
        $product->category_product_building_id = 4;
        $product->name = 'Double Silver ';
        $product->description = '80%';
        $product->save();
    }
}
