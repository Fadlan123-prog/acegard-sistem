<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreativeTask extends Model
{
    use Auditable;

    protected $auditModule = 'CreativeTask';

    protected $fillable = [
        'assigned_by', 'assigned_to', 'title', 'brief',
        'department_context', 'priority', 'due_date', 'status',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(CreativeTaskDeliverable::class)->orderByDesc('version');
    }

    public function latestDeliverable(): ?CreativeTaskDeliverable
    {
        return $this->deliverables->first();
    }

    public function nextVersion(): int
    {
        return ($this->deliverables()->max('version') ?? 0) + 1;
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isBeforeToday() && $this->status !== 'done';
    }
}
