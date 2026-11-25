<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchRollPrice extends Model
{
    protected $fillable = ['category_product_id', 'category_product_building_id', 'price', 'unit'];

    protected $casts = ['price' => 'decimal:2'];
}
