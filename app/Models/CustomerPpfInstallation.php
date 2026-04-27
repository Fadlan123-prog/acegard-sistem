<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPpfInstallation extends Model
{
    protected $table = 'customer_ppf_installations';

    protected $fillable = [
        'customer_ppf_id',
        'ppf_product_id',
        'branch_id',
        'install_type',
        'install_date',
        'warranty_code',
        'warranty_start',
        'warranty_years',
        'warranty_end',
        'warranty_status',
        'panels',
        'checklist_image',
        'job_order_image',
        'notes',
    ];

    protected $casts = [
        'panels' => 'array',
        'install_date' => 'date',
        'warranty_start' => 'date',
        'warranty_end' => 'date',
    ];

    public function customer()
    {
        // karena FK = customer_ppf_id dan tabel parent = customer_ppf
        return $this->belongsTo(\App\Models\CustomerPpf::class, 'customer_ppf_id');
    }

    public function customerPpf()
    {
        return $this->belongsTo(\App\Models\CustomerPpf::class, 'customer_ppf_id');
    }

    public function ppfProduct()
    {
        return $this->belongsTo(PpfProduct::class, 'ppf_product_id');
    }

    public function technicians()
    {
        return $this->belongsToMany(
            Employee::class,
            'customer_ppf_installation_technicians',
            'customer_ppf_installation_id',
            'employee_id'
        )->withTimestamps();
    }

    // kalau kamu sudah punya invoice PPF:
    public function invoice()
    {
        return $this->hasOne(PpfInvoice::class, 'customer_ppf_installation_id');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
    }
}
