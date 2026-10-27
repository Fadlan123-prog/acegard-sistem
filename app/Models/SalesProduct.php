<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

/**
 * Katalog produk yang hanya bisa dijual lewat jaringan sales (tidak dijual
 * bebas di cabang). `category` generik dari awal — kategori baru cukup
 * ditambah sebagai baris lewat UI, tidak perlu migration.
 */
class SalesProduct extends Model
{
    use Auditable;

    protected $fillable = [
        'name',
        'category',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price'     => 'integer',
        'is_active' => 'boolean',
    ];

    public function transactions()
    {
        return $this->hasMany(SalesTransaction::class);
    }
}
