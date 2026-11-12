<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerBuildingTechnicianMeter extends Model
{
    protected $table = 'customer_building_technician_meters';

    protected $fillable = ['customer_building_id', 'employee_id', 'category_product_building_id', 'meters'];

    protected $casts = ['meters' => 'float'];

    public function customer()
    {
        return $this->belongsTo(CustomerBuilding::class, 'customer_building_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function category()
    {
        return $this->belongsTo(CategoryProductBuilding::class, 'category_product_building_id');
    }
}
