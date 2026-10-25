<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class WarrantyClaim extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'claim_number','claim_date','warranty_type',
        'warrantable_type','warrantable_id',
        'branch_id','card_number',
        'cause','status','is_chargeable','quote_total',
        'complaint','inspection_notes','resolution_notes',
    ];

    public function warrantable()
    {
        return $this->morphTo();
    }

    public function items()
    {
        return $this->hasMany(WarrantyClaimItem::class);
    }

    public function invoice()
    {
        return $this->hasOne(\App\Models\WarrantyClaimInvoice::class, 'warranty_claim_id');
    }
}

