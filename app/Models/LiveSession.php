<?php

namespace App\Models;

use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Model;

class LiveSession extends Model
{
    use HasBranchScope;

    protected $fillable = [
        'branch_id', 'employee_id', 'session_date', 'duration_minutes',
        'viewers_peak', 'viewers_avg', 'chat_count', 'product_questions',
        'leads_generated', 'closing_count', 'revenue', 'notes', 'created_by',
    ];

    protected $casts = [
        'session_date' => 'date',
        'revenue'      => 'decimal:2',
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

    public function getDurationHoursAttribute(): float
    {
        return round($this->duration_minutes / 60, 1);
    }
}
