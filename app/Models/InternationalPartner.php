<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternationalPartner extends Model
{
    protected $fillable = ['type', 'name', 'country', 'contact_person', 'phone', 'email', 'default_currency'];

    public function importOrders(): HasMany
    {
        return $this->hasMany(ImportOrder::class);
    }

    public function exportOrders(): HasMany
    {
        return $this->hasMany(ExportOrder::class);
    }

    public function scopeSuppliers($query)
    {
        return $query->where('type', 'supplier');
    }

    public function scopeBuyers($query)
    {
        return $query->where('type', 'buyer');
    }
}
