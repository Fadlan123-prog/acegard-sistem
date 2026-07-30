<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class CommissionPaymentBatch extends Model
{
    use Auditable;

    protected $table = 'commission_payment_batches';

    protected $fillable = [
        'employee_id',
        'batch_number',
        'period_from',
        'period_to',
        'payment_date',
        'status',
        'total_amount',
        'created_by',
        'paid_by',
        'paid_at',
        'notes',
        'requester_notes',
    ];

    protected $casts = [
        'period_from'  => 'date',
        'period_to'    => 'date',
        'payment_date' => 'date',
        'paid_at'      => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class, 'commission_payment_batch_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paidByUser()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'draft';
    }

    public function isApproved(): bool
    {
        return $this->status === 'paid';
    }

    public function isRejected(): bool
    {
        return $this->status === 'cancelled';
    }
}
