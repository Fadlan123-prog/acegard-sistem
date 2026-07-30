<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilmComplaintItem extends Model
{
    protected $fillable = [
        'film_complaint_id',

        'category_product_id',
        'product_id',
        'part_id',

        'replacement_side',
        'replacement_scope',
        'coverage_percent',

        'category_product_building_id',
        'product_building_id',

        'area_meter',
        'used_length_meter',
        'waste_percent',
        'usage_source',
        'notes',
    ];

    protected $casts = [
        'coverage_percent' => 'decimal:2',
        'area_meter' => 'decimal:2',
        'used_length_meter' => 'decimal:2',
        'waste_percent' => 'decimal:2',
    ];

    public function complaint()
    {
        return $this->belongsTo(FilmComplaint::class, 'film_complaint_id');
    }

    public function categoryProduct()
    {
        return $this->belongsTo(CategoryProduct::class, 'category_product_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function categoryProductBuilding()
    {
        return $this->belongsTo(CategoryProductBuilding::class, 'category_product_building_id');
    }

    public function productBuilding()
    {
        return $this->belongsTo(ProductBuilding::class, 'product_building_id');
    }
}
