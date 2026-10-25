<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportOrderItem extends Model
{
    protected $fillable = ['import_order_id', 'item_name', 'quantity', 'unit', 'unit_price_foreign'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ImportOrder::class, 'import_order_id');
    }

    public function lineTotalForeign(): float
    {
        return $this->quantity * $this->unit_price_foreign;
    }
}
