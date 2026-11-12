<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiScore extends Model
{
    protected $fillable = [
        'employee_id', 'kpi_target_id', 'actual_value', 'created_by',
    ];

    protected $casts = [
        'actual_value' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function target()
    {
        return $this->belongsTo(KpiTarget::class, 'kpi_target_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function achievementPercent(): float
    {
        return $this->target->achievementPercent((float) $this->actual_value);
    }

    public function weightedScore(): float
    {
        return round($this->achievementPercent() * (float) $this->target->weight / 100, 2);
    }
}
