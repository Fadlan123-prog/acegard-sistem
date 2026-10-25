<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use Auditable, HasFactory;
    protected $table = 'invoices';

    protected $fillable = [
        'customer_id',
        'payment_method',
        'price',
        'discount',
        'downpayment',
        'total_price',
        'remaining_payment',
        'status',
        'work_status',
        'qc_status',
        'qc_notes',
        'qc_reviewed_by',
        'qc_reviewed_at',
        'invoice_number',
        'invoice_date',
        'accepted_at',
        'completed_at',
        'paid_at',
        'pelunasan_at',
        'pelunasan_by',
        'commission_released_at',
        'commission_released_by',
        'stock_deducted_at',
        'stock_deducted_by',
        'job_photo',
        'checklist_photo',
    ];

    protected $casts = [
        'accepted_at'            => 'datetime',
        'completed_at'           => 'datetime',
        'paid_at'                => 'datetime',
        'pelunasan_at'           => 'datetime',
        'commission_released_at' => 'datetime',
        'stock_deducted_at'      => 'datetime',
        'qc_reviewed_at'         => 'datetime',
    ];

    // ─── Step helpers ─────────────────────────────────────────────────────────

    public function isStep1Done(): bool
    {
        // Invoice exists = step 1 done
        return true;
    }

    public function isStep2Done(): bool
    {
        return $this->pelunasan_at !== null || $this->status === 'paid';
    }

    public function isStep3Done(): bool
    {
        return $this->commission_released_at !== null;
    }

    public function isStockDeducted(): bool
    {
        return $this->stock_deducted_at !== null;
    }

    public function currentStep(): int
    {
        if ($this->isStep3Done()) return 3;
        if ($this->isStep2Done()) return 2;
        return 1;
    }

    // ─── QC gate ────────────────────────────────────────────────────────────
    // qc_status is intentionally null for any invoice completed before this gate
    // existed — only isQcBlocking() is used to guard pelunasan, and null never blocks.

    public function isQcPassed(): bool
    {
        return $this->qc_status === 'passed';
    }

    public function isQcFailed(): bool
    {
        return $this->qc_status === 'failed';
    }

    public function isQcBlocking(): bool
    {
        return in_array($this->qc_status, ['pending', 'failed'], true);
    }

    public function qcReviewer()
    {
        return $this->belongsTo(User::class, 'qc_reviewed_by');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function commissions()
    {
        return $this->hasMany(\App\Models\Commission::class);
    }
}
