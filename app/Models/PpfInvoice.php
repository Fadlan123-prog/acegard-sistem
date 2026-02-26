<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpfInvoice extends Model
{
    protected $table = 'ppf_invoices';

    protected $fillable = [
        'branch_id','customer_ppf_id','customer_ppf_installation_id',
        'invoice_number','invoice_date','payment_method',
        'price','discount','downpayment','total_price','remaining_payment',
        'status','notes'
    ];

    protected $casts = [
        'invoice_date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function customer()
    {
        return $this->belongsTo(CustomerPpf::class, 'customer_ppf_id');
    }

    public function installation()
    {
        return $this->belongsTo(CustomerPpfInstallation::class, 'customer_ppf_installation_id');
    }
}
