<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $product = new Product();
        $product->category_id = 1;
        $product->name = 'AP50';
        $product->description = '20%';
        $product->save();

        $product = new Product();
        $product->category_id = 1;
        $product->name = 'AP35';
        $product->description = '40%';
        $product->save();

        $product = new Product();
        $product->category_id = 1;
        $product->name = 'AP20';
        $product->description = '60%';
        $product->save();

        $product = new Product();
        $product->category_id = 1;
        $product->name = 'AP05';
        $product->description = '80%';
        $product->save();

        $product = new Product();
        $product->category_id = 2;
        $product->name = 'NV70';
        $product->description = '20%';
        $product->save();

        $product = new Product();
        $product->category_id = 2;
        $product->name = 'NV60';
        $product->description = '30%';
        $product->save();

        $product = new Product();
        $product->category_id = 2;
        $product->name = 'NV40';
        $product->description = '40%';
        $product->save();

        $product = new Product();
        $product->category_id = 2;
        $product->name = 'NV15';
        $product->description = '60%';
        $product->save();

        $product = new Product();
        $product->category_id = 2;
        $product->name = 'NV05';
        $product->description = '80%';
        $product->save();

        $product = new Product();
        $product->category_id = 3;
        $product->name = 'BM40';
        $product->description = '40%';
        $product->save();

        $product = new Product();
        $product->category_id = 3;
        $product->name = 'BM15';
        $product->description = '60%';
        $product->save();

        $product = new Product();
        $product->category_id = 3;
        $product->name = 'BM05';
        $product->description = '80%';
        $product->save();


    }
}
