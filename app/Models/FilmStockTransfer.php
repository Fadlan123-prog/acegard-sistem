<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class FilmStockTransfer extends Model
{
    use Auditable;

    protected $fillable = [
        'transfer_number',
        'from_branch_id',
        'to_branch_id',
        'transfer_date',
        'status',
        'created_by',
        'received_by',
        'notes',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(FilmStockTransferItem::class);
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
