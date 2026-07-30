<?php

namespace App\Models;

use App\Traits\HasBranchScope;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasBranchScope, SoftDeletes, Auditable;

    protected $fillable = [
        'branch_id', 'quotation_id', 'lead_id',
        'so_number', 'spk_number', 'spk_token', 'spk_issued_at',
        'date', 'scheduled_date',
        'prospect_name', 'prospect_phone', 'prospect_email', 'prospect_address',
        'category', 'vehicle_brand', 'vehicle_model', 'vehicle_year', 'vehicle_plate',
        'tukang_id', 'kenek_id', 'marketing_id',
        'subtotal', 'discount_amount', 'total_amount',
        'status', 'confirmed_at', 'in_progress_at', 'completed_at', 'cancelled_at', 'cancel_reason',
        'converted_to_type', 'converted_to_id', 'converted_at',
        'notes', 'created_by',
    ];

    protected $casts = [
        'date'            => 'date',
        'scheduled_date'  => 'date',
        'confirmed_at'    => 'datetime',
        'in_progress_at'  => 'datetime',
        'completed_at'    => 'datetime',
        'cancelled_at'    => 'datetime',
        'converted_at'    => 'datetime',
        'spk_issued_at'   => 'datetime',
    ];

    const STATUSES = [
        'pending'     => 'Menunggu',
        'confirmed'   => 'Dikonfirmasi',
        'in_progress' => 'Dikerjakan',
        'completed'   => 'Selesai',
        'cancelled'   => 'Dibatalkan',
    ];

    const STATUS_COLORS = [
        'pending'     => 'neutral',
        'confirmed'   => 'info',
        'in_progress' => 'warning',
        'completed'   => 'success',
        'cancelled'   => 'danger',
    ];

    const CATEGORIES = [
        'car_film'      => 'Kaca Film Mobil',
        'building_film' => 'Kaca Film Gedung',
        'ppf'           => 'PPF',
    ];

    // ── Accessors ────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'neutral';
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    // ── Status helpers ───────────────────────────────────────────────────

    public function isPending(): bool     { return $this->status === 'pending'; }
    public function isConfirmed(): bool   { return $this->status === 'confirmed'; }
    public function isInProgress(): bool  { return $this->status === 'in_progress'; }
    public function isCompleted(): bool   { return $this->status === 'completed'; }
    public function isCancelled(): bool   { return $this->status === 'cancelled'; }
    public function isEditable(): bool    { return in_array($this->status, ['pending', 'confirmed']); }
    public function isConverted(): bool   { return $this->converted_at !== null; }
    public function hasSpk(): bool        { return $this->spk_number !== null; }

    public function getSpkPublicUrlAttribute(): ?string
    {
        return $this->spk_token ? route('spk.public', $this->spk_token) : null;
    }

    // ── Recalculate totals from items ────────────────────────────────────

    public function recalculate(): void
    {
        $subtotal = $this->items()->get()->sum(fn ($i) => $i->calculateSubtotal());
        $this->update([
            'subtotal'     => $subtotal,
            'total_amount' => max(0, $subtotal - $this->discount_amount),
        ]);
    }

    // ── Relations ────────────────────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class)->orderBy('sort_order');
    }

    public function tukang(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'tukang_id');
    }

    public function kenek(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'kenek_id');
    }

    public function marketing(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'marketing_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function convertedTo(): MorphTo
    {
        return $this->morphTo('converted_to');
    }
}
