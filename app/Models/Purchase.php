<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use Auditable, SoftDeletes;

    // Status constants — covers both legacy ('confirmed') and new GRN flow
    const STATUS_DRAFT            = 'draft';
    const STATUS_CONFIRMED        = 'confirmed';       // legacy alias for sent
    const STATUS_SENT             = 'sent';
    const STATUS_PARTIAL_RECEIVED = 'partial_received';
    const STATUS_RECEIVED         = 'received';
    const STATUS_MATCHED          = 'matched';
    const STATUS_CANCELLED        = 'cancelled';

    // Statuses that allow a new goods receipt to be created
    const RECEIVABLE_STATUSES = ['confirmed', 'sent', 'partial_received'];

    protected $fillable = [
        'branch_id',
        'supplier_id',
        'user_id',
        'purchase_number',
        'purchase_date',
        'total_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function receipts()
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    public function approvals()
    {
        return $this->hasMany(PurchaseApproval::class);
    }

    public function isReceivable(): bool
    {
        return in_array($this->status, self::RECEIVABLE_STATUSES);
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isFullyReceived(): bool
    {
        return in_array($this->status, [self::STATUS_RECEIVED, self::STATUS_MATCHED]);
    }
}
