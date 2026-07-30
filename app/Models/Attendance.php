<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'branch_id', 'employee_id', 'created_by',
        'shift_id', 'leave_request_id',
        'attendance_date', 'status', 'daily_salary', 'notes',
        'check_in_at', 'check_out_at',
        'check_in_lat', 'check_in_lng',
        'check_out_lat', 'check_out_lng',
        'check_in_photo',
        'late_minutes', 'overtime_minutes',
    ];

    protected $casts = [
        'attendance_date'  => 'date',
        'daily_salary'     => 'integer',
        'check_in_at'      => 'datetime',
        'check_out_at'     => 'datetime',
        'late_minutes'     => 'integer',
        'overtime_minutes' => 'integer',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }
}
