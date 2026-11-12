<?php

namespace App\Models;

use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Model;

class ContentPost extends Model
{
    use HasBranchScope;

    protected $fillable = [
        'branch_id', 'employee_id', 'platform', 'title', 'published_at',
        'views', 'engagement_rate', 'leads_generated', 'is_fyp', 'notes', 'created_by',
    ];

    protected $casts = [
        'published_at'    => 'date',
        'engagement_rate'  => 'decimal:2',
        'is_fyp'           => 'boolean',
    ];

    const PLATFORMS = [
        'tiktok'    => 'TikTok',
        'instagram' => 'Instagram',
        'youtube'   => 'YouTube',
        'website'   => 'Website',
    ];

    const FYP_VIEWS_THRESHOLD = 100000;

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

    public function getPlatformLabelAttribute(): string
    {
        return self::PLATFORMS[$this->platform] ?? $this->platform;
    }
}
