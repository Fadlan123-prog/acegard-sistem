<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportOrder extends Model
{
    use Auditable;

    protected $auditModule = 'ImportOrder';

    protected $fillable = [
        'international_partner_id', 'order_number', 'currency', 'exchange_rate',
        'subtotal_foreign', 'duty_cost', 'tax_cost', 'freight_cost', 'insurance_cost',
        'total_cost_idr', 'status', 'order_date', 'estimated_arrival', 'received_date', 'created_by',
    ];

    protected $casts = [
        'exchange_rate'     => 'decimal:4',
        'order_date'        => 'date',
        'estimated_arrival' => 'date',
        'received_date'     => 'date',
    ];

    public static array $statusLabels = [
        'ordered'  => 'Dipesan',
        'shipped'  => 'Dikirim',
        'customs'  => 'Di Bea Cukai',
        'received' => 'Diterima',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(InternationalPartner::class, 'international_partner_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ImportOrderItem::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ImportOrderDocument::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isReceived(): bool
    {
        return $this->status === 'received';
    }
}
