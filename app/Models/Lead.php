<?php

namespace App\Models;

use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes, HasBranchScope;

    protected $fillable = [
        'branch_id', 'name', 'phone', 'email', 'address', 'city',
        'source', 'category', 'vehicle_brand', 'vehicle_model', 'vehicle_year',
        'notes', 'status', 'lost_reason', 'assigned_to', 'estimated_value',
        'follow_up_date', 'quoted_at', 'converted_at',
        'converted_to_type', 'converted_to_id', 'created_by',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'follow_up_date'  => 'date',
        'quoted_at'       => 'datetime',
        'converted_at'    => 'datetime',
    ];

    const SOURCES = [
        'walk_in'      => 'Walk In',
        'referral'     => 'Referral',
        'social_media' => 'Social Media',
        'website'      => 'Website',
        'whatsapp'     => 'WhatsApp',
        'cold_call'    => 'Cold Call',
        'other'        => 'Lainnya',
    ];

    const CATEGORIES = [
        'car_film'      => 'Kaca Film Mobil',
        'building_film' => 'Kaca Film Gedung',
        'ppf'           => 'PPF',
    ];

    const STATUSES = [
        'new'        => 'Baru',
        'contacted'  => 'Dihubungi',
        'follow_up'  => 'Follow Up',
        'quoted'     => 'Penawaran Dikirim',
        'won'        => 'Berhasil',
        'lost'       => 'Gagal',
    ];

    const STATUS_COLORS = [
        'new'       => 'primary',
        'contacted' => 'info',
        'follow_up' => 'warning',
        'quoted'    => 'secondary',
        'won'       => 'success',
        'lost'      => 'danger',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function assignee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class)->latest();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source] ?? $this->source;
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function isConverted(): bool
    {
        return $this->converted_at !== null;
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeFollowUpToday($query)
    {
        return $query->whereDate('follow_up_date', today());
    }
}
