<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id', 'branch_id', 'requested_by', 'approved_by',
        'leave_type', 'from_date', 'to_date', 'days',
        'reason', 'status', 'approval_notes', 'approved_at',
    ];

    protected $casts = [
        'from_date'   => 'date',
        'to_date'     => 'date',
        'approved_at' => 'datetime',
    ];

    public static array $typeLabels = [
        'annual'    => 'Cuti Tahunan',
        'sick'      => 'Sakit',
        'permission'=> 'Izin',
        'emergency' => 'Darurat',
        'libur'     => 'Libur',
    ];

    // Attendance status written when leave is approved
    public static array $typeToAttendanceStatus = [
        'annual'    => 'off',
        'sick'      => 'sick',
        'permission'=> 'permission',
        'emergency' => 'permission',
        'libur'     => 'off',
    ];

    public function employee()  { return $this->belongsTo(Employee::class); }
    public function branch()    { return $this->belongsTo(Branch::class); }
    public function requester() { return $this->belongsTo(User::class, 'requested_by'); }
    public function approver()  { return $this->belongsTo(User::class, 'approved_by'); }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'leave_request_id');
    }

    public function isPending():  bool { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
}
