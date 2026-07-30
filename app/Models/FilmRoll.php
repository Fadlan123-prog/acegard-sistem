<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilmRoll extends Model
{
    protected $fillable = [
        'branch_id',

        'category_product_id',
        'product_id',

        'category_product_building_id',
        'product_building_id',

        'roll_code',
        'film_type',
        'darkness_code',
        'usage_for',

        'width_meter',
        'length_meter',
        'initial_area_meter',
        'remaining_length_meter',
        'remaining_area_meter',

        'status',
        'received_date',
        'notes',
    ];

    protected $casts = [
        'width_meter' => 'decimal:2',
        'length_meter' => 'decimal:2',
        'initial_area_meter' => 'decimal:2',
        'remaining_length_meter' => 'decimal:2',
        'remaining_area_meter' => 'decimal:2',
        'received_date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function usages()
    {
        return $this->hasMany(FilmRollUsage::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productBuilding()
    {
        return $this->belongsTo(ProductBuilding::class, 'product_building_id');
    }
}
