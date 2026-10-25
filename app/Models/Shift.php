<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'branch_id', 'name', 'start_time', 'end_time',
        'overtime_after_minutes', 'is_active',
    ];

    protected $casts = [
        'is_active'              => 'boolean',
        'overtime_after_minutes' => 'integer',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /** Duration of this shift in minutes (handles overnight shifts). */
    public function shiftMinutes(): int
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end   = \Carbon\Carbon::parse($this->end_time);
        if ($end->lte($start)) $end->addDay();
        return (int) $start->diffInMinutes($end);
    }
}
