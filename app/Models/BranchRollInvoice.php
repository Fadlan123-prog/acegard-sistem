<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchRollInvoice extends Model
{
    protected $fillable = [
        'invoice_number', 'from_branch_id', 'to_branch_id', 'invoice_date', 'due_date',
        'delivery_status', 'payment_status', 'paid_at', 'total_amount', 'notes',
        'created_by', 'received_by', 'received_at', 'cancelled_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date'     => 'date',
        'paid_at'      => 'datetime',
        'received_at'  => 'datetime',
        'cancelled_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function fromBranch() { return $this->belongsTo(Branch::class, 'from_branch_id'); }
    public function toBranch()   { return $this->belongsTo(Branch::class, 'to_branch_id'); }
    public function creator()    { return $this->belongsTo(User::class, 'created_by'); }
    public function receiver()   { return $this->belongsTo(User::class, 'received_by'); }
    public function items()      { return $this->hasMany(BranchRollInvoiceItem::class); }

    public function getDeliveryLabelAttribute(): string
    {
        return ['sent' => 'Dalam Pengiriman', 'received' => 'Diterima', 'cancelled' => 'Dibatalkan'][$this->delivery_status] ?? $this->delivery_status;
    }
}
