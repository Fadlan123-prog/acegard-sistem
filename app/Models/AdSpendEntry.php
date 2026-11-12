<?php

namespace App\Models;

use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Model;

class AdSpendEntry extends Model
{
    use HasBranchScope;

    protected $fillable = [
        'branch_id', 'channel', 'spend_date', 'amount', 'notes', 'created_by',
    ];

    protected $casts = [
        'spend_date' => 'date',
        'amount'     => 'decimal:2',
    ];

    const CHANNELS = [
        'meta'    => 'Meta Ads',
        'tiktok'  => 'TikTok Ads',
        'google'  => 'Google Ads',
        'lainnya' => 'Lainnya',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getChannelLabelAttribute(): string
    {
        return self::CHANNELS[$this->channel] ?? $this->channel;
    }
}
