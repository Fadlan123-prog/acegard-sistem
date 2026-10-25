<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeHistory extends Model
{
    protected $fillable = [
        'employee_id', 'recorded_by', 'event_type',
        'from_value', 'to_value', 'effective_date', 'notes',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    public static array $eventLabels = [
        'hired'         => 'Bergabung',
        'promoted'      => 'Promosi',
        'transferred'   => 'Mutasi',
        'resigned'      => 'Resign',
        'terminated'    => 'PHK',
        'salary_change' => 'Perubahan Gaji',
        'status_change' => 'Perubahan Status',
    ];

    public function employee()  { return $this->belongsTo(Employee::class); }
    public function recorder()  { return $this->belongsTo(User::class, 'recorded_by'); }
}
