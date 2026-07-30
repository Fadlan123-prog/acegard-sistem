<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class PurchaseReceipt extends Model
{
    use Auditable;

    protected $fillable = [
        'purchase_id',
        'received_by',
        'receipt_number',
        'receipt_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'receipt_date' => 'date',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseReceiptItem::class, 'receipt_id');
    }
}
