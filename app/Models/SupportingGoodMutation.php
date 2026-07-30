<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportingGoodMutation extends Model
{
    protected $fillable = [
        'supporting_good_id',
        'branch_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'quantity'     => 'decimal:2',
        'stock_before' => 'decimal:2',
        'stock_after'  => 'decimal:2',
    ];

    public function supportingGood()
    {
        return $this->belongsTo(SupportingGood::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'in'     => 'Barang Masuk',
            'out'    => 'Pemakaian',
            'opname' => 'Stock Opname',
            default  => $this->type,
        };
    }
}
