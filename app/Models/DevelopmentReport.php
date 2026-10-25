<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevelopmentReport extends Model
{
    use Auditable;

    protected $auditModule = 'Development';

    protected $fillable = ['created_by', 'title', 'content', 'period_start', 'period_end'];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
