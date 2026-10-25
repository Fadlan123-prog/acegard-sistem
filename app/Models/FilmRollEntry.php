<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilmRollEntry extends Model
{
    protected $fillable = [
        'film_stock_pool_id',
        'branch_id',
        'current_branch_id',
        'roll_code',
        'film_type',
        'darkness_code',
        'width_meter',
        'length_meter',
        'area_meter',
        'status',
        'received_date',
        'notes',
    ];

    protected $casts = [
        'width_meter' => 'decimal:2',
        'length_meter' => 'decimal:2',
        'area_meter' => 'decimal:2',
        'received_date' => 'date',
    ];

    public function stockPool()
    {
        return $this->belongsTo(FilmStockPool::class, 'film_stock_pool_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function currentBranch()
    {
        return $this->belongsTo(Branch::class, 'current_branch_id');
    }
}
