<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;
use App\Traits\HasBranchScope;

class Customer extends Model
{
    use Auditable, HasBranchScope, HasFactory;

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
        'has_panoramic',
        'tukang_id',
        'kenek_id',
        'marketing_id',
        'vehicle_year',
        'warantee_start',
        'warantee_end',
        'warantee_duration',
        'install_date',
    ];

    protected $casts = [
        'install_date'   => 'date',
        'has_panoramic'  => 'boolean',
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

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Latest invoice ordered by id — used in the 3-step flow where one
    // invoice is the "active" work order for a customer at a time.
    public function latestInvoice()
    {
        return $this->hasOne(Invoice::class)->latestOfMany();
    }

    // Backward-compatibility shim — existing code calling $customer->invoice
    // continues to work and gets the most recent invoice.
    public function invoice()
    {
        return $this->latestInvoice();
    }

    public function tukang()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'tukang_id');
    }

    public function kenek()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'kenek_id');
    }

    public function marketing()
    {
        return $this->belongsTo(Employee::class, 'marketing_id');
    }
}
