<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasbonDeduction extends Model
{
    protected $fillable = [
        'kasbon_id', 'source', 'payroll_id', 'commission_payment_batch_id', 'amount', 'period',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function kasbon()
    {
        return $this->belongsTo(Kasbon::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function commissionPaymentBatch()
    {
        return $this->belongsTo(CommissionPaymentBatch::class);
    }
}
