<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionOverrideTrigger extends Model
{
    protected $table = 'commission_override_triggers';

    protected $fillable = [
        'override_employee_id',
        'trigger_marketing_id',
        'rate',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'rate'      => 'float',
        'is_active' => 'boolean',
    ];

    public function overrideEmployee()
    {
        return $this->belongsTo(Employee::class, 'override_employee_id');
    }

    public function triggerMarketing()
    {
        return $this->belongsTo(Employee::class, 'trigger_marketing_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
