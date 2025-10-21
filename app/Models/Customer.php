<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasBranchScope;

class Customer extends Model
{
    use HasBranchScope;

    protected $fillable = [
        'branch_id',
        'wsn',
        'card_number',
        'name',
        'email',
        'phone_number',
        'address',
        'dealer_name',
        'applicator',
        'city',
        'country',
        'vehicle_brand',
        'vehicle_model',
        'plat_number',
        'install_type',
        'tukang_id',
        'kenek_id',
        'marketing_id',
        'vehicle_year',
        'warantee_start',
        'warantee_end',
        'warantee_duration',
    ];

    protected $table = 'customers';

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customerProducts()
    {
        return $this->hasMany(CustomerProduct::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'customer_product')
                    ->withPivot('part_id', 'category_product_id')
                    ->withTimestamps();
    }

    public function invoice() // satu invoice saja jika hanya satu
    {
        return $this->hasOne(Invoice::class);
    }

    public function tukang()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'tukang_id');
    }

    public function kenek()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'kenek_id');
    }
}
