<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id', 'sort_order', 'description', 'specifications', 'part_ids', 'film_category_id', 'film_product_id',
        'unit', 'quantity', 'unit_price', 'discount_percent', 'subtotal',
    ];

    protected $casts = [
        'part_ids'         => 'array',
        'quantity'         => 'decimal:2',
        'unit_price'       => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'subtotal'         => 'decimal:2',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function calculateSubtotal(): float
    {
        $base = (float) $this->quantity * (float) $this->unit_price;
        $disc = $base * ((float) $this->discount_percent / 100);
        return round($base - $disc, 2);
    }
}
