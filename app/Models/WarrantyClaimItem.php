<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarrantyClaimItem extends Model
{
    protected $fillable = [
        'warranty_claim_id','item_type','item_id','item_key',
        'item_label','action','charge_amount','notes'
    ];

    public function claim()
    {
        return $this->belongsTo(WarrantyClaim::class, 'warranty_claim_id');
    }
}
