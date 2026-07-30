<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilmStockUsage extends Model
{
    protected $fillable = [
        'film_stock_pool_id',
        'film_complaint_id',
        'branch_id',

        'customer_id',
        'customer_product_id',

        'customer_building_id',
        'customer_building_product_id',

        'category_product_id',
        'product_id',
        'part_id',

        'category_product_building_id',
        'product_building_id',

        'usage_type',
        'usage_reason',

        'film_type',
        'darkness_code',
        'install_type',

        'vehicle_brand',
        'vehicle_model',
        'plat_number',

        'area_meter',
        'used_length_meter',
        'waste_percent',

        'stock_before',
        'stock_after',

        'usage_source',
        'usage_date',
        'notes',
    ];

    protected $casts = [
        'area_meter' => 'decimal:2',
        'used_length_meter' => 'decimal:2',
        'waste_percent' => 'decimal:2',
        'stock_before' => 'decimal:2',
        'stock_after' => 'decimal:2',
        'usage_date' => 'date',
    ];

    public function stockPool()
    {
        return $this->belongsTo(FilmStockPool::class, 'film_stock_pool_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerBuilding()
    {
        return $this->belongsTo(CustomerBuilding::class, 'customer_building_id');
    }

    public function complaint()
    {
        return $this->belongsTo(FilmComplaint::class, 'film_complaint_id');
    }
}
