<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Phase 3 stub — controller and views not yet implemented.
class PurchaseApproval extends Model
{
    protected $fillable = [
        'purchase_id',
        'requested_by',
        'approved_by',
        'status',
        'threshold_amount',
        'notes',
        'approved_at',
    ];

    protected $casts = [
        'threshold_amount' => 'decimal:2',
        'approved_at'      => 'datetime',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
}
