<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class InvoiceBuilding extends Model
{
    use Auditable;
    protected $table = 'invoice_building';

    protected $fillable = [
        'customer_building_id', 'branch_id', 'user_id',
        'type', 'price', 'discount', 'down_payment', 'total_price',
        'remaining_payment', 'status', 'invoice_number', 'invoice_date',
        'pelunasan_at', 'pelunasan_by',
        'stock_deducted_at', 'stock_deducted_by',
        'waste_percent',
    ];

    protected $casts = [
        'pelunasan_at'     => 'datetime',
        'stock_deducted_at'=> 'datetime',
        'waste_percent'    => 'float',
    ];

    // ─── Step helpers ──────────────────────────────────────────────────────

    public function isStep1Done(): bool { return true; }

    public function isStep2Done(): bool
    {
        return $this->pelunasan_at !== null || $this->status === 'paid';
    }

    public function isStockDeducted(): bool
    {
        return $this->stock_deducted_at !== null;
    }

    public function currentStep(): int
    {
        if ($this->isStep2Done()) return 2;
        return 1;
    }

    public function customer()
    {
        return $this->belongsTo(CustomerBuilding::class, 'customer_building_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(CustomerBuildingProduct::class, 'invoice_building_product')
                    ->withPivot('price')
                    ->withTimestamps();
    }
}
