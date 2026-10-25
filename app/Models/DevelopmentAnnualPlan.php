<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DevelopmentAnnualPlan extends Model
{
    use Auditable;

    protected $auditModule = 'Development';

    protected $fillable = ['created_by', 'year', 'summary'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function initiatives(): HasMany
    {
        return $this->hasMany(DevelopmentAnnualPlanInitiative::class, 'plan_id');
    }
}
