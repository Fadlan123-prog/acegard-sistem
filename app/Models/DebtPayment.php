<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class DebtPayment extends Model
{
    use Auditable;

    protected $fillable = [
        'payable_type', 'payable_id',
        'amount', 'payment_date', 'payment_method', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'amount'       => 'integer',
        'payment_date' => 'date',
    ];

    public function payable()
    {
        return $this->morphTo();
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static array $methods = [
        'cash'     => 'Cash',
        'transfer' => 'Transfer Bank',
        'qris'     => 'QRIS',
        'cek'      => 'Cek / Giro',
    ];
}
