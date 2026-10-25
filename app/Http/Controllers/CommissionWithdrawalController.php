<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Commission;
use App\Models\CommissionPaymentBatch;
use App\Services\BranchContext;
use App\Services\KasbonDeductionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionWithdrawalController extends Controller
{
    public function __construct(
        private BranchContext $branch,
        private KasbonDeductionService $kasbonDeductions,
    ) {}

    private function authorizeBatch(CommissionPaymentBatch $batch): void
    {
        $branchId = $this->branch->idOrNull();
        if ($branchId === null) {
            return; // superadmin — no restriction
        }

        $batch->loadMissing('employee:id,branch_id');
        abort_unless((int) ($batch->employee?->branch_id) === (int) $branchId, 403);
    }

    public function index(Request $request)
    {
        $status   = $request->get('status', 'draft');
        $branchId = $this->branch->idOrNull();
        $branch   = $branchId ? Branch::find($branchId) : null;

        $batches = CommissionPaymentBatch::query()
            ->with(['employee:id,name,job_position', 'creator:id,name'])
            ->whereNotNull('employee_id')
            ->when($branchId, fn($q) => $q->whereHas('employee', fn($eq) => $eq->where('branch_id', $branchId)))
            ->when($status !== '', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('commission.withdrawals', [
            'title'   => 'Pengajuan Pencairan Komisi',
            'batches' => $batches,
            'filters' => ['status' => $status],
            'branch'  => $branch,
        ]);
    }

    public function show(CommissionPaymentBatch $batch)
    {
        $this->authorizeBatch($batch);

        $batch->load([
            'employee:id,name,job_position',
            'creator:id,name',
            'paidByUser:id,name',
            'commissions.customer:id,name,vehicle_brand,vehicle_model,plat_number,install_type',
            'commissions.invoice:id,invoice_number,status',
        ]);

        return view('commission.withdrawal-detail', [
            'title' => 'Detail Pencairan #' . $batch->batch_number,
            'batch' => $batch,
        ]);
    }

    public function approve(Request $request, CommissionPaymentBatch $batch)
    {
        $this->authorizeBatch($batch);

        if ($batch->status !== 'draft') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($batch, $request) {
            $now = now();

            $batch->update([
                'status'  => 'paid',
                'paid_by' => auth()->id(),
                'paid_at' => $now,
                'notes'   => $request->input('notes'),
            ]);

            Commission::where('commission_payment_batch_id', $batch->id)
                ->where('status', 'eligible')
                ->update([
                    'status'  => 'paid',
                    'paid_at' => $now,
                    'paid_by' => auth()->id(),
                    'updated_at' => $now,
                ]);

            // Kasbon potong-dari-komisi: tidak mengubah total_amount batch ini
            // (akurasi histori komisi tetap utuh) — cuma dicatat di ledger,
            // ditampilkan sebagai "Dipotong Kasbon" terpisah di halaman detail.
            if ($batch->employee) {
                $this->kasbonDeductions->applyForCommission($batch->employee, $batch);
            }
        });

        return redirect()
            ->route('commission.withdrawals.index')
            ->with('success', "Pencairan #{$batch->batch_number} untuk {$batch->employee?->name} berhasil disetujui.");
    }

    public function reject(Request $request, CommissionPaymentBatch $batch)
    {
        $this->authorizeBatch($batch);

        if ($batch->status !== 'draft') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'notes' => ['required', 'string', 'max:500'],
        ], [
            'notes.required' => 'Alasan penolakan wajib diisi.',
        ]);

        DB::transaction(function () use ($batch, $request) {
            $batch->update([
                'status' => 'cancelled',
                'notes'  => $request->input('notes'),
            ]);

            // Unbatch the commissions — they go back to eligible so technician can re-request
            Commission::where('commission_payment_batch_id', $batch->id)
                ->update([
                    'commission_payment_batch_id' => null,
                    'updated_at'                  => now(),
                ]);
        });

        return redirect()
            ->route('commission.withdrawals.index')
            ->with('success', "Pencairan #{$batch->batch_number} berhasil ditolak. Komisi dikembalikan ke status eligible.");
    }
}
