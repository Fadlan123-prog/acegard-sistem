<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarrantyClaimInvoice extends Model
{
    protected $fillable = [
        'warranty_claim_id','invoice_number','invoice_date',
        'payment_method','subtotal','discount','downpayment',
        'total_price','remaining_payment','status'
    ];

    public function claim()
    {
        return $this->belongsTo(WarrantyClaim::class, 'warranty_claim_id');
    }

    public function items()
    {
        return $this->hasMany(WarrantyClaimInvoiceItem::class, 'warranty_claim_invoice_id');
    }
}
