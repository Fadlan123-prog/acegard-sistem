<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerBuildingProduct extends Model
{
    protected $table = 'customer_building_product';

    protected $fillable = [
        'customer_building_id',
        'product_building_id',
        'category_product_building_id',
        'meters',
        'warantee_start',
        'warantee_end',
        'warantee_duration',

    ];

    public function invoices()
    {
        return $this->belongsToMany(InvoiceBuilding::class, 'invoice_building_product')
                    ->withPivot('price')
                    ->withTimestamps();
    }

    public function product()
    {
        return $this->belongsTo(ProductBuilding::class, 'product_building_id');
    }

    public function category()
    {
        return $this->belongsTo(CategoryProductBuilding::class, 'category_product_building_id');
    }

    public function customer()
    {
        return $this->belongsTo(CustomerBuilding::class, 'customer_building_id');
    }

    public function categoryProductBuilding(){
        return $this->belongsTo(CategoryProductBuilding::class, 'category_product_building_id');
    }

}
