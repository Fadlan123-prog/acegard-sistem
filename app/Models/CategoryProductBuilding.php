<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryProductBuilding extends Model
{
    protected $table = 'category_product_building';

    protected $fillable = [
        'customer_building_id',
        'product_building_id',
        'category_product_building_id',
        'meters',
        'warantee_start',
        'warantee_end',
        'warantee_duration',
    ];


}
