<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerBuilding extends Model
{
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
}
