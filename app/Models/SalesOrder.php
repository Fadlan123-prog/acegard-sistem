<?php

namespace App\Models;

use App\Traits\HasBranchScope;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'tukang_id', 'kenek_id', 'marketing_id', 'film_category_id', 'film_product_id',
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

    /**
     * Kaca Film Gedung and PPF can both have a team of more than 2 people on
     * the job (pivot `technicians`, no cap) — only car_film stays limited to
     * a single tukang_id + kenek_id pair. Centralised here because the same
     * branch used to be written out as `$category === 'building_film'` in
     * six different places (controller + 2 Blade views) and PPF was missing
     * from every one of them.
     */
    /** Kategori yang memilih tipe film & kegelapan (mobil / gedung). */
    public function usesFilmSelection(): bool
    {
        return in_array($this->category, ['car_film', 'building_film'], true);
    }

    public function filmCategoryModel(): ?string
    {
        return match ($this->category) {
            'car_film'      => \App\Models\CategoryProduct::class,
            'building_film' => \App\Models\CategoryProductBuilding::class,
            default         => null,
        };
    }

    public function filmProductModel(): ?string
    {
        return match ($this->category) {
            'car_film'      => \App\Models\Product::class,
            'building_film' => \App\Models\ProductBuilding::class,
            default         => null,
        };
    }

    public function getFilmCategoryAttribute()
    {
        $m = $this->filmCategoryModel();

        return ($m && $this->film_category_id) ? $m::find($this->film_category_id) : null;
    }

    public function getFilmProductAttribute()
    {
        $m = $this->filmProductModel();

        return ($m && $this->film_product_id) ? $m::find($this->film_product_id) : null;
    }

    /** Contoh: "Premium — AP50 (20%)" */
    public function getFilmLabelAttribute(): ?string
    {
        $cat = $this->film_category;
        $prod = $this->film_product;

        if (! $cat && ! $prod) {
            return null;
        }

        $prodLabel = $prod ? trim($prod->name . ($prod->description ? ' (' . $prod->description . ')' : '')) : null;

        return trim(implode(' — ', array_filter([$cat?->name, $prodLabel])));
    }

    public function assignsMultipleTechnicians(): bool
    {
        return in_array($this->category, ['building_film', 'ppf'], true);
    }

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

    /**
     * Penugasan multi-teknisi — dipakai kategori Kaca Film Gedung (tidak ada kenek,
     * bisa lebih dari 2 orang). Untuk car_film/ppf, penugasan tetap pakai tukang()/kenek().
     */
    public function technicians(): BelongsToMany
    {
        return $this->belongsToMany(
            Employee::class,
            'sales_order_technicians',
            'sales_order_id',
            'employee_id'
        )->withTimestamps();
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
