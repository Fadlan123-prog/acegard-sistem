<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class Employee extends Model
{
    use Auditable, SoftDeletes, HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'branch_id',
        'name',
        'phone_number',
        'npwp',
        'job_position',
        'monthly_salary',
        'is_active',
        'hire_date',
        'employment_status',
        'meal_allowance_enabled',
        'meal_allowance_rate',
        'ppf_admin_commission',
        'mobile_token',
        'bpjs_ketenagakerjaan_no',
        'bpjs_ketenagakerjaan_enrolled',
        'jkk_risk_group',
        'bpjs_kesehatan_no',
        'bpjs_kesehatan_enrolled',
    ];

    protected $casts = [
        'is_active'                     => 'boolean',
        'ppf_admin_commission'         => 'boolean',
        'bpjs_ketenagakerjaan_enrolled' => 'boolean',
        'bpjs_kesehatan_enrolled'       => 'boolean',
        'hire_date'                     => 'date',
        'jkk_risk_group'                => 'integer',
    ];

    public function commissions()
    {
        return $this->hasMany(Commission::class, 'employees_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function overrideTriggers()
    {
        return $this->hasMany(\App\Models\CommissionOverrideTrigger::class, 'override_employee_id');
    }

    public function marketingTriggers()
    {
        return $this->hasMany(\App\Models\CommissionOverrideTrigger::class, 'trigger_marketing_id');
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function histories()
    {
        return $this->hasMany(EmployeeHistory::class)->orderByDesc('effective_date');
    }
}
