<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReceiptItem extends Model
{
    protected $fillable = [
        'receipt_id',
        'purchase_item_id',
        'qty_received',
        'notes',
    ];

    protected $casts = [
        'qty_received' => 'decimal:2',
    ];

    public function receipt()
    {
        return $this->belongsTo(PurchaseReceipt::class, 'receipt_id');
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_item_id');
    }
}
