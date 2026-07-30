<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilmStockTransferItem extends Model
{
    protected $fillable = [
        'film_stock_transfer_id',
        'film_roll_entry_id',
        'film_stock_pool_id',
        'roll_code',
        'film_type',
        'darkness_code',
        'width_meter',
        'length_meter',
        'area_meter',
    ];

    protected $casts = [
        'width_meter' => 'decimal:2',
        'length_meter' => 'decimal:2',
        'area_meter' => 'decimal:2',
    ];

    public function transfer()
    {
        return $this->belongsTo(FilmStockTransfer::class, 'film_stock_transfer_id');
    }

    public function rollEntry()
    {
        return $this->belongsTo(FilmRollEntry::class, 'film_roll_entry_id');
    }

    public function stockPool()
    {
        return $this->belongsTo(FilmStockPool::class, 'film_stock_pool_id');
    }
}
