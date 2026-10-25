<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportOrderItem extends Model
{
    protected $fillable = ['export_order_id', 'item_name', 'quantity', 'unit', 'unit_price_foreign'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ExportOrder::class, 'export_order_id');
    }

    public function lineTotalForeign(): float
    {
        return $this->quantity * $this->unit_price_foreign;
    }
}
