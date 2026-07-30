<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilmRollUsage extends Model
{
    protected $fillable = [
        'film_roll_id',
        'branch_id',

        'customer_id',
        'customer_building_id',

        'customer_product_id',
        'customer_building_product_id',

        'category_product_id',
        'product_id',
        'part_id',

        'category_product_building_id',
        'product_building_id',

        'usage_type',
        'install_type',

        'vehicle_brand',
        'vehicle_model',
        'plat_number',

        'area_meter',
        'estimated_length_meter',
        'actual_length_meter',
        'waste_percent',

        'roll_remaining_before',
        'roll_remaining_after',

        'usage_source',
        'usage_date',
        'notes',
    ];

    protected $casts = [
        'area_meter' => 'decimal:2',
        'estimated_length_meter' => 'decimal:2',
        'actual_length_meter' => 'decimal:2',
        'waste_percent' => 'decimal:2',
        'roll_remaining_before' => 'decimal:2',
        'roll_remaining_after' => 'decimal:2',
        'usage_date' => 'date',
    ];

    public function filmRoll()
    {
        return $this->belongsTo(FilmRoll::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerBuilding()
    {
        return $this->belongsTo(CustomerBuilding::class, 'customer_building_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productBuilding()
    {
        return $this->belongsTo(ProductBuilding::class, 'product_building_id');
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }
}
