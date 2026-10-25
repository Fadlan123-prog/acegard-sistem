<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarrantyClaimInvoiceItem extends Model
{
    protected $fillable = [
        'warranty_claim_invoice_id','description','qty','unit_price','total'
    ];

    public function invoice()
    {
        return $this->belongsTo(WarrantyClaimInvoice::class, 'warranty_claim_invoice_id');
    }
}
