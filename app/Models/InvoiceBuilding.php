<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceBuilding extends Model
{
    protected $table = 'invoice_building';

    protected $fillable = [
        'customer_building_id', 'branch_id', 'user_id',
        'type', 'price', 'discount', 'down_payment', 'total_price',
        'remaining_payment', 'status', 'invoice_number', 'invoice_date'
    ];

    public function customer()
    {
        return $this->belongsTo(CustomerBuilding::class, 'customer_building_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(CustomerBuildingProduct::class, 'invoice_building_product')
                    ->withPivot('price')
                    ->withTimestamps();
    }
}
