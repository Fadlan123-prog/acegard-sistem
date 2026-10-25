<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasApprovalFlow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class EmployeeClaim extends Model
{
    use Auditable, HasApprovalFlow;

    protected $auditModule = 'EmployeeClaim';

    protected $fillable = [
        'employee_id', 'branch_id', 'type', 'amount', 'description', 'proof_path',
        'status', 'petty_cash_transaction_id', 'disbursed_by', 'disbursed_at',
    ];

    protected $casts = [
        'disbursed_at' => 'datetime',
    ];

    public static array $typeLabels = [
        'pembelian' => 'Klaim Pembelian',
        'bensin'    => 'Klaim Bensin',
        'kesehatan' => 'Klaim Biaya Kesehatan',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function pettyCashTransaction(): BelongsTo
    {
        return $this->belongsTo(PettyCashTransaction::class);
    }

    public function disburser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /** Shown in the generic approval queue (approvals/index.blade.php). */
    public function approvalSummary(): string
    {
        $label = self::$typeLabels[$this->type] ?? $this->type;
        $employeeName = $this->employee?->name ?? '-';

        return "{$label} — {$employeeName} — Rp " . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Called by ApprovalService once the approval_flow's last step is decided.
     * Keeps this model's own status in sync with the generic approval system,
     * and — only for 'kesehatan' claims, only once truly approved (not on
     * submit, so a rejected claim never touches the balance) — deducts the
     * approved amount from the employee's health balance for the year the
     * claim was made in.
     */
    public function onApprovalDecision(string $decision, $approvalRequest): void
    {
        if ($decision === 'approved') {
            $this->update(['status' => 'approved']);

            if ($this->type === 'kesehatan') {
                DB::transaction(function () {
                    $year = $this->created_at->year;

                    $balance = EmployeeHealthBalance::where('employee_id', $this->employee_id)
                        ->where('year', $year)
                        ->lockForUpdate()
                        ->first();

                    if ($balance) {
                        $balance->increment('used_amount', $this->amount);
                    }
                });
            }
        } elseif ($decision === 'rejected') {
            $this->update(['status' => 'rejected']);
        }
    }
}
