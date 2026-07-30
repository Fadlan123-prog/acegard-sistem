<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilmStockPool extends Model
{
    protected $fillable = [
        'branch_id',

        'category_product_id',
        'product_id',

        'category_product_building_id',
        'product_building_id',

        'film_type',
        'darkness_code',
        'usage_for',

        'width_meter',

        'total_received_length_meter',
        'total_used_length_meter',
        'remaining_length_meter',
        'remaining_area_meter',
    ];

    protected $casts = [
        'width_meter' => 'decimal:2',
        'total_received_length_meter' => 'decimal:2',
        'total_used_length_meter' => 'decimal:2',
        'remaining_length_meter' => 'decimal:2',
        'remaining_area_meter' => 'decimal:2',
    ];

    public function rollEntries()
    {
        return $this->hasMany(FilmRollEntry::class);
    }

    public function usages()
    {
        return $this->hasMany(FilmStockUsage::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }


}
