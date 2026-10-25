<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasApprovalFlow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DebtPayment extends Model
{
    use Auditable, HasApprovalFlow;

    protected $fillable = [
        'payable_type', 'payable_id',
        'amount', 'payment_date', 'payment_method', 'notes', 'recorded_by', 'status',
    ];

    protected $casts = [
        'amount'       => 'integer',
        'payment_date' => 'date',
    ];

    public function payable()
    {
        return $this->morphTo();
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static array $methods = [
        'cash'     => 'Cash',
        'transfer' => 'Transfer Bank',
        'qris'     => 'QRIS',
        'cek'      => 'Cek / Giro',
    ];

    public static array $statusLabels = [
        'pending_approval' => 'Menunggu Approval',
        'approved'         => 'Disetujui',
        'rejected'         => 'Ditolak',
    ];

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    /** Shown in the generic approval queue (approvals/index.blade.php). */
    public function approvalSummary(): string
    {
        $record = $this->payable;
        $isPayable = $this->payable_type === Payable::class;
        $label = $isPayable ? 'Pembayaran Hutang' : 'Pencairan Piutang';
        $name = $isPayable ? ($record?->vendor_name ?? '-') : ($record?->customer_name ?? '-');

        return "{$label} — {$name} — Rp " . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Called by ApprovalService once the approval_flow's last step is decided.
     * A payment is recorded as 'pending_approval' and never touches the parent
     * Payable/Receivable's paid_amount until the Owner approves it here — under
     * lockForUpdate(), mirroring the same overpayment-race guard that used to
     * live in HutangPiutangController::recordPayment() before this gate existed.
     */
    public function onApprovalDecision(string $decision, $approvalRequest): void
    {
        if ($decision === 'approved') {
            DB::transaction(function () {
                $record = $this->payable()->lockForUpdate()->first();

                if ($this->amount > $record->remaining()) {
                    throw ValidationException::withMessages([
                        'approval' => 'Jumlah pembayaran melebihi sisa tagihan saat ini (kemungkinan sudah dibayar di request lain).',
                    ]);
                }

                $record->update(['paid_amount' => $record->paid_amount + $this->amount]);
                $record->update(['status' => match (true) {
                    $record->paid_amount >= $record->amount => 'paid',
                    $record->paid_amount > 0                => 'partial',
                    default                                  => 'unpaid',
                }]);

                $this->update(['status' => 'approved']);
            });
        } elseif ($decision === 'rejected') {
            $this->update(['status' => 'rejected']);
        }
    }
}
