<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';

    protected $fillable = [
        'customer_id',
        'payment_method',
        'price',
        'discount',
        'downpayment',
        'total_price',
        'remaining_payment',
        'status',
        'invoice_number',
        'invoice_date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function commissions()
    {
        return $this->hasMany(\App\Models\Commission::class);
    }
}
