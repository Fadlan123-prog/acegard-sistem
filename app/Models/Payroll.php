<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use Auditable;
    protected $fillable = [
        'branch_id',
        'month',
        'working_days',
        'status',
        'generated_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
