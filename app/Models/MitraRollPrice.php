<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MitraRollPrice extends Model
{
    protected $fillable = [
        'category_product_building_id',
        'price_mitra',
        'price_non_franchise',
        'unit',
    ];

    public function category()
    {
        return $this->belongsTo(CategoryProductBuilding::class, 'category_product_building_id');
    }

    public function invoiceItems()
    {
        return $this->hasMany(MitraInvoiceItem::class);
    }

    public function priceFor(string $mitraType): float
    {
        return (float) ($mitraType === 'non_franchise' ? $this->price_non_franchise : $this->price_mitra);
    }
}
