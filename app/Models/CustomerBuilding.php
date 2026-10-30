<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerBuilding extends Model
{
    use HasFactory;

    protected $table = 'customer_building';

    protected $fillable = [
        'branch_id', 'wsn', 'card_number', 'name', 'email',
        'phone_number', 'address', 'dealer_name', 'applicator',
        'city', 'country', 'warantee_start', 'warantee_end', 'warantee_duration'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function invoices()
    {
        return $this->hasMany(InvoiceBuilding::class);
    }

    public function products()
    {
        return $this->hasMany(CustomerBuildingProduct::class);
    }

    public function technicianMeters()
    {
        return $this->hasMany(CustomerBuildingTechnicianMeter::class, 'customer_building_id');
    }

    public function part(){
        return $this->hasManyThrough(Part::class, ProductBuilding::class);
    }

    public function categoryProductBuildings(){
        return $this->hasMany(CategoryProductBuilding::class);
    }

    public function customerBuildingProducts()
    {
        return $this->hasMany(CustomerBuildingProduct::class, 'customer_building_id', 'id');
    }

}
