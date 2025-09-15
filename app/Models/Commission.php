<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $table = 'commissions';

    protected $fillable = ['employees_id','customer_id','invoice_id','amount'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employees_id');
    }
    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
    public function invoice()
    {
        return $this->belongsTo(\App\Models\Invoice::class);
    }
}
