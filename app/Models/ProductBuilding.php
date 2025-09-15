<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBuilding extends Model
{
    protected $table = 'product_building';

    public function categoryBuildingProduct()
    {
        return $this->hasMany(CategoryBuildingProduct::class, 'id', 'category_building_product_id');
    }
}
