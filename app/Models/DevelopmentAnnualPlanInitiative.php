<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevelopmentAnnualPlanInitiative extends Model
{
    protected $fillable = ['plan_id', 'title', 'description', 'target_date', 'status'];

    protected $casts = [
        'target_date' => 'date',
    ];

    public static array $statusLabels = [
        'planned'     => 'Direncanakan',
        'in_progress' => 'Berjalan',
        'done'        => 'Selesai',
        'cancelled'   => 'Dibatalkan',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(DevelopmentAnnualPlan::class, 'plan_id');
    }
}
