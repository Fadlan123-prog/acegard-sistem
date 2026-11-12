<?php

namespace App\Models;

use App\Traits\HasBranchScope;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use SoftDeletes, HasBranchScope, Auditable;

    protected $fillable = [
        'branch_id', 'lead_id', 'quotation_number', 'date', 'valid_until',
        'prospect_name', 'prospect_phone', 'prospect_email', 'prospect_address',
        'category', 'vehicle_brand', 'vehicle_model', 'vehicle_year', 'vehicle_plate',
        'notes', 'terms', 'status',
        'subtotal', 'discount_amount', 'total_amount',
        'assigned_to', 'created_by',
        'sent_at', 'accepted_at', 'rejected_at', 'rejection_reason', 'converted_at',
    ];

    protected $casts = [
        'date'          => 'date',
        'valid_until'   => 'date',
        'subtotal'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'  => 'decimal:2',
        'sent_at'       => 'datetime',
        'accepted_at'   => 'datetime',
        'rejected_at'   => 'datetime',
        'converted_at'  => 'datetime',
    ];

    const STATUSES = [
        'draft'    => 'Draft',
        'sent'     => 'Terkirim',
        'accepted' => 'Diterima',
        'rejected' => 'Ditolak',
        'expired'  => 'Kedaluwarsa',
    ];

    const STATUS_COLORS = [
        'draft'    => 'secondary',
        'sent'     => 'info',
        'accepted' => 'success',
        'rejected' => 'danger',
        'expired'  => 'warning',
    ];

    const CATEGORIES = [
        'car_film'      => 'Kaca Film Mobil',
        'building_film' => 'Kaca Film Gedung',
        'ppf'           => 'PPF',
    ];

    const DEFAULT_TERMS = "1. Penawaran ini berlaku sesuai tanggal validitas yang tertera.\n2. Harga dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.\n3. Garansi produk sesuai ketentuan merek yang digunakan.\n4. Pembayaran DP minimal 50% saat konfirmasi pesanan.";

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function assignee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        $status = $this->resolvedStatus();
        return self::STATUS_COLORS[$status] ?? 'secondary';
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function resolvedStatus(): string
    {
        if ($this->status === 'sent' && $this->valid_until->isBeforeToday()) {
            return 'expired';
        }
        return $this->status;
    }

    public function getResolvedStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->resolvedStatus()] ?? $this->resolvedStatus();
    }

    public function isDraft(): bool    { return $this->status === 'draft'; }
    public function isSent(): bool     { return $this->status === 'sent'; }
    public function isAccepted(): bool { return $this->status === 'accepted'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
    public function isConverted(): bool { return $this->converted_at !== null; }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    public function recalculate(): void
    {
        $this->load('items');
        $subtotal = $this->items->sum('subtotal');
        $this->subtotal      = $subtotal;
        $this->total_amount  = $subtotal - $this->discount_amount;
        $this->save();
    }
}
