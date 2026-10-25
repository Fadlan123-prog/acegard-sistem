<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    use Auditable;
    protected $fillable = [
        'payroll_id',
        'employee_id',
        'employee_name',
        'job_position',

        'monthly_salary',
        'daily_prorate',

        'present_days',
        'off_days',
        'off_quota',
        'paid_off_days',
        'excess_off_days',
        'sick_days',
        'permission_days',
        'absent_days',

        'paid_days',
        'prorate_salary',

        'transport_allowance',
        'meal_allowance',

        'main_deduction',
        'cash_advance',
        'attendance_deduction',
        'other_deduction',

        'overtime_minutes',
        'overtime_pay',
        'late_deduction',

        'bpjs_jht_employee',
        'bpjs_jp_employee',
        'bpjs_kes_employee',
        'bpjs_total_employee',
        'bpjs_jkk_employer',
        'bpjs_jkm_employer',
        'bpjs_jht_employer',
        'bpjs_jp_employer',
        'bpjs_kes_employer',
        'bpjs_total_employer',

        'total_salary',
        'rounded_salary',

        'notes',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
