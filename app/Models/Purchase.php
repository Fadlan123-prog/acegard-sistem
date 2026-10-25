<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasApprovalFlow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use Auditable, HasApprovalFlow, SoftDeletes;

    // Status constants — covers both legacy ('confirmed') and new GRN flow
    const STATUS_DRAFT            = 'draft';
    const STATUS_CONFIRMED        = 'confirmed';       // legacy alias for sent
    const STATUS_SENT             = 'sent';
    const STATUS_PARTIAL_RECEIVED = 'partial_received';
    const STATUS_RECEIVED         = 'received';
    const STATUS_MATCHED          = 'matched';
    const STATUS_CANCELLED        = 'cancelled';

    // Statuses that allow a new goods receipt to be created
    const RECEIVABLE_STATUSES = ['confirmed', 'sent', 'partial_received'];

    protected $fillable = [
        'branch_id',
        'supplier_id',
        'user_id',
        'purchase_number',
        'purchase_date',
        'total_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function receipts()
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    public function approvals()
    {
        return $this->hasMany(PurchaseApproval::class);
    }

    public function isReceivable(): bool
    {
        return in_array($this->status, self::RECEIVABLE_STATUSES);
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isFullyReceived(): bool
    {
        return in_array($this->status, [self::STATUS_RECEIVED, self::STATUS_MATCHED]);
    }

    /** Shown in the generic approval queue (approvals/index.blade.php). */
    public function approvalSummary(): string
    {
        $supplierName = $this->supplier?->name ?? '-';

        return "PO {$this->purchase_number} — {$supplierName} — Rp " . number_format($this->total_amount, 0, ',', '.');
    }

    /**
     * Called by ApprovalService once the approval_flow's last step is decided.
     * A purchase is saved as 'draft' when submitted for confirm-approval
     * (see PurchaseController); this flips it to 'confirmed' only once the
     * Owner approves, or leaves it as 'draft' if rejected.
     */
    public function onApprovalDecision(string $decision, $approvalRequest): void
    {
        if ($decision === 'approved') {
            $this->update(['status' => self::STATUS_CONFIRMED]);
        } elseif ($decision === 'rejected') {
            $this->update(['status' => self::STATUS_DRAFT]);
        }
    }
}
