<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasApprovalFlow;
use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Model;

class Kasbon extends Model
{
    use Auditable, HasApprovalFlow, HasBranchScope;

    protected $auditModule = 'Kasbon';

    protected $fillable = [
        'employee_id', 'branch_id', 'amount', 'reason',
        'installments', 'installment_amount', 'deduction_source',
        'status', 'remaining_amount', 'installments_paid', 'requested_by',
    ];

    protected $casts = [
        'amount'              => 'integer',
        'installment_amount'  => 'integer',
        'remaining_amount'    => 'integer',
    ];

    public static array $sourceLabels = [
        'payroll'    => 'Gajian',
        'commission' => 'Komisi',
        'both'       => 'Gajian & Komisi',
    ];

    public static array $statusLabels = [
        'pending_approval' => 'Menunggu Approval',
        'active'           => 'Berjalan',
        'rejected'         => 'Ditolak',
        'cancelled'        => 'Dibatalkan',
        'completed'        => 'Lunas',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function deductions()
    {
        return $this->hasMany(KasbonDeduction::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** Shown in the generic approval queue (approvals/index.blade.php). */
    public function approvalSummary(): string
    {
        $employeeName = $this->employee?->name ?? '-';
        $source = self::$sourceLabels[$this->deduction_source] ?? $this->deduction_source;

        return "Kasbon — {$employeeName} — Rp " . number_format($this->amount, 0, ',', '.')
            . " ({$this->installments}x, potong {$source})";
    }

    /**
     * Called by ApprovalService once the approval_flow's last step is decided.
     * No money moves here — approval only flips status to 'active', the
     * installments themselves are deducted later by KasbonDeductionService
     * whenever payroll/commission for this employee is actually processed.
     */
    public function onApprovalDecision(string $decision, $approvalRequest): void
    {
        if ($decision === 'approved') {
            $this->update(['status' => 'active']);
        } elseif ($decision === 'rejected') {
            $this->update(['status' => 'rejected']);
        }
    }
}
