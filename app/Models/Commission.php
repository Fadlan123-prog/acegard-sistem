<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commission extends Model
{
    use Auditable, HasFactory;
    protected $table = 'commissions';

    protected $fillable = [
        'employees_id',
        'customer_id',
        'invoice_id',
        'commission_period_id',
        'amount',
        'role',
        'category_product_id',
        'status',
        'is_active',
        'eligible_at',
        'paid_at',
        'paid_by',
        'commission_payment_batch_id',
        'payment_notes',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employees_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function batch()
    {
        return $this->belongsTo(CommissionPaymentBatch::class, 'commission_payment_batch_id');
    }

    public function commissionPeriod()
    {
        return $this->belongsTo(CommissionPeriod::class);
    }
}
