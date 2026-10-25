<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MitraInvoiceItem extends Model
{
    protected $fillable = [
        'mitra_invoice_id',
        'mitra_roll_price_id',
        'product_building_id',
        'item_name',
        'qty',
        'unit',
        'unit_price',
        'total_price',
    ];

    public function invoice()
    {
        return $this->belongsTo(MitraInvoice::class, 'mitra_invoice_id');
    }

    public function rollPrice()
    {
        return $this->belongsTo(MitraRollPrice::class, 'mitra_roll_price_id');
    }

    public function productBuilding()
    {
        return $this->belongsTo(ProductBuilding::class, 'product_building_id');
    }
}
