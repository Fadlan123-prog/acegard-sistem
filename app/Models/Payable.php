<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Payable extends Model
{
    use Auditable;

    protected $fillable = [
        'branch_id', 'supplier_id', 'vendor_name', 'reference_number',
        'description', 'amount', 'paid_amount', 'due_date', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'amount'      => 'integer',
        'paid_amount' => 'integer',
        'due_date'    => 'date',
    ];

    public function branch()    { return $this->belongsTo(Branch::class); }
    public function supplier()  { return $this->belongsTo(Supplier::class); }
    public function creator()   { return $this->belongsTo(User::class, 'created_by'); }

    public function payments()
    {
        return $this->morphMany(DebtPayment::class, 'payable');
    }

    public function remaining(): int
    {
        return $this->amount - $this->paid_amount;
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date && $this->due_date->isPast();
    }

    public static array $statusLabels = [
        'unpaid'  => 'Belum Bayar',
        'partial' => 'Sebagian',
        'paid'    => 'Lunas',
    ];
}
