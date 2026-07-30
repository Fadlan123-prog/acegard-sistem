<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderItem extends Model
{
    protected $fillable = [
        'sales_order_id', 'sort_order', 'description', 'specifications',
        'unit', 'quantity', 'unit_price', 'discount_percent', 'subtotal',
    ];

    protected $casts = [
        'quantity'         => 'float',
        'discount_percent' => 'float',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function calculateSubtotal(): int
    {
        return (int) round($this->quantity * $this->unit_price * (1 - $this->discount_percent / 100));
    }
}
