<?php

namespace App\Http\Controllers;

use App\Exports\HutangExport;
use App\Exports\PiutangExport;
use App\Models\Branch;
use App\Models\DebtPayment;
use App\Models\Payable;
use App\Models\Receivable;
use App\Models\Supplier;
use App\Services\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class HutangPiutangController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    // ── Hutang (Payables) ─────────────────────────────────────────────────────

    public function hutangIndex(Request $request)
    {
        $user     = auth()->user();
        $branchId = $this->activeBranchId($user, $request);
        $branches = $this->branchesForUser($user);

        $query = Payable::with(['branch', 'supplier'])
            ->when(!$user->hasRole('superadmin'), fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->filled('branch_id') && $user->hasRole('superadmin'),
                fn ($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->filled('status'),
                fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'),
                fn ($q) => $q->where('vendor_name', 'like', '%'.$request->search.'%'))
            ->orderByRaw("FIELD(status,'unpaid','partial','paid')")
            ->orderBy('due_date');

        $payables = $query->paginate(20)->withQueryString();

        $summary = $this->buildSummary(Payable::class, $branchId, $user->hasRole('superadmin'), $request->get('branch_id'));

        return view('hutang-piutang.hutang.index', [
            'payables'     => $payables,
            'branches'     => $branches,
            'branchId'     => $branchId,
            'isSuperadmin' => $user->hasRole('superadmin'),
            'summary'      => $summary,
            'filters'      => $request->only(['search', 'status', 'branch_id']),
        ]);
    }

    public function hutangCreate(Request $request)
    {
        $user     = auth()->user();
        $branches = $this->branchesForUser($user);
        $branchId = $this->activeBranchId($user, $request);
        $suppliers = Supplier::orderBy('name')->get();

        return view('hutang-piutang.hutang.create', compact('branches', 'branchId', 'suppliers') + [
            'isSuperadmin' => $user->hasRole('superadmin'),
        ]);
    }

    public function hutangStore(Request $request)
    {
        $data = $request->validate([
            'branch_id'        => 'required|exists:branches,id',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'vendor_name'      => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'description'      => 'required|string',
            'amount'           => 'required|integer|min:1',
            'due_date'         => 'nullable|date',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $this->authorizeBranch((int) $data['branch_id']);

        $data['paid_amount'] = 0;
        $data['status']      = 'unpaid';
        $data['created_by']  = auth()->id();

        Payable::create($data);

        return redirect()->route('hutang.index')
            ->with('success', 'Hutang berhasil dicatat.');
    }

    public function hutangShow(Payable $payable)
    {
        $this->authorizeRecord($payable->branch_id);
        $payable->load(['branch', 'supplier', 'payments.recorder']);

        return view('hutang-piutang.hutang.show', ['payable' => $payable]);
    }

    public function hutangEdit(Payable $payable)
    {
        $this->authorizeRecord($payable->branch_id);
        $suppliers = Supplier::orderBy('name')->get();

        return view('hutang-piutang.hutang.edit', [
            'payable'      => $payable,
            'suppliers'    => $suppliers,
            'isSuperadmin' => auth()->user()->hasRole('superadmin'),
        ]);
    }

    public function hutangUpdate(Request $request, Payable $payable)
    {
        $this->authorizeRecord($payable->branch_id);

        $data = $request->validate([
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'vendor_name'      => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'description'      => 'required|string',
            'amount'           => 'required|integer|min:1',
            'due_date'         => 'nullable|date',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $payable->update($data);
        $this->recalcStatus($payable);

        return redirect()->route('hutang.show', $payable)
            ->with('success', 'Hutang berhasil diperbarui.');
    }

    public function hutangDestroy(Payable $payable)
    {
        $this->authorizeRecord($payable->branch_id);

        if ($payable->paid_amount > 0) {
            return back()->with('error', 'Hutang yang sudah ada pembayaran tidak dapat dihapus.');
        }

        $payable->delete();

        return redirect()->route('hutang.index')->with('success', 'Hutang berhasil dihapus.');
    }

    public function hutangExport(Request $request)
    {
        $user     = auth()->user();
        $branchId = $this->activeBranchId($user, $request);
        $filters  = array_filter(['branch_id' => $branchId, 'status' => $request->status]);

        return Excel::download(new HutangExport($filters), 'hutang-'.now()->format('Ymd').'.xlsx');
    }

    // ── Piutang (Receivables) ─────────────────────────────────────────────────

    public function piutangIndex(Request $request)
    {
        $user     = auth()->user();
        $branchId = $this->activeBranchId($user, $request);
        $branches = $this->branchesForUser($user);

        $query = Receivable::with('branch')
            ->when(!$user->hasRole('superadmin'), fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->filled('branch_id') && $user->hasRole('superadmin'),
                fn ($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->filled('status'),
                fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'),
                fn ($q) => $q->where('customer_name', 'like', '%'.$request->search.'%'))
            ->orderByRaw("FIELD(status,'unpaid','partial','paid')")
            ->orderBy('due_date');

        $receivables = $query->paginate(20)->withQueryString();

        $summary = $this->buildSummary(Receivable::class, $branchId, $user->hasRole('superadmin'), $request->get('branch_id'));

        return view('hutang-piutang.piutang.index', [
            'receivables'  => $receivables,
            'branches'     => $branches,
            'branchId'     => $branchId,
            'isSuperadmin' => $user->hasRole('superadmin'),
            'summary'      => $summary,
            'filters'      => $request->only(['search', 'status', 'branch_id']),
        ]);
    }

    public function piutangCreate(Request $request)
    {
        $user     = auth()->user();
        $branches = $this->branchesForUser($user);
        $branchId = $this->activeBranchId($user, $request);

        return view('hutang-piutang.piutang.create', compact('branches', 'branchId') + [
            'isSuperadmin' => $user->hasRole('superadmin'),
        ]);
    }

    public function piutangStore(Request $request)
    {
        $data = $request->validate([
            'branch_id'        => 'required|exists:branches,id',
            'customer_name'    => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'description'      => 'required|string',
            'amount'           => 'required|integer|min:1',
            'due_date'         => 'nullable|date',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $this->authorizeBranch((int) $data['branch_id']);

        $data['paid_amount'] = 0;
        $data['status']      = 'unpaid';
        $data['created_by']  = auth()->id();

        Receivable::create($data);

        return redirect()->route('piutang.index')
            ->with('success', 'Piutang berhasil dicatat.');
    }

    public function piutangShow(Receivable $receivable)
    {
        $this->authorizeRecord($receivable->branch_id);
        $receivable->load(['branch', 'payments.recorder']);

        return view('hutang-piutang.piutang.show', ['receivable' => $receivable]);
    }

    public function piutangEdit(Receivable $receivable)
    {
        $this->authorizeRecord($receivable->branch_id);

        return view('hutang-piutang.piutang.edit', [
            'receivable'   => $receivable,
            'isSuperadmin' => auth()->user()->hasRole('superadmin'),
        ]);
    }

    public function piutangUpdate(Request $request, Receivable $receivable)
    {
        $this->authorizeRecord($receivable->branch_id);

        $data = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'description'      => 'required|string',
            'amount'           => 'required|integer|min:1',
            'due_date'         => 'nullable|date',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $receivable->update($data);
        $this->recalcStatus($receivable);

        return redirect()->route('piutang.show', $receivable)
            ->with('success', 'Piutang berhasil diperbarui.');
    }

    public function piutangDestroy(Receivable $receivable)
    {
        $this->authorizeRecord($receivable->branch_id);

        if ($receivable->paid_amount > 0) {
            return back()->with('error', 'Piutang yang sudah ada pembayaran tidak dapat dihapus.');
        }

        $receivable->delete();

        return redirect()->route('piutang.index')->with('success', 'Piutang berhasil dihapus.');
    }

    public function piutangExport(Request $request)
    {
        $user     = auth()->user();
        $branchId = $this->activeBranchId($user, $request);
        $filters  = array_filter(['branch_id' => $branchId, 'status' => $request->status]);

        return Excel::download(new PiutangExport($filters), 'piutang-'.now()->format('Ymd').'.xlsx');
    }

    // ── Payments (shared) ─────────────────────────────────────────────────────

    public function recordPayment(Request $request, int $id, string $type)
    {
        $record = $type === 'hutang'
            ? Payable::findOrFail($id)
            : Receivable::findOrFail($id);

        $this->authorizeRecord($record->branch_id);

        $remaining = $record->remaining();

        // Fast-path UX check against a possibly-stale $remaining — the
        // authoritative check happens under lockForUpdate() once the Owner
        // approves (see DebtPayment::onApprovalDecision()), so two payments
        // approved back-to-back can't overdraw the same outstanding balance.
        $data = $request->validate([
            'amount'         => "required|integer|min:1|max:{$remaining}",
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,transfer,qris,cek',
            'notes'          => 'nullable|string|max:255',
        ]);

        // Pembayaran Hutang/Piutang butuh approval Owner sebelum benar-benar
        // mengurangi sisa tagihan — dicatat dulu sebagai pending_approval.
        $payment = DebtPayment::create([
            'payable_type'   => get_class($record),
            'payable_id'     => $record->id,
            'amount'         => $data['amount'],
            'payment_date'   => $data['payment_date'],
            'payment_method' => $data['payment_method'],
            'notes'          => $data['notes'] ?? null,
            'recorded_by'    => auth()->id(),
            'status'         => 'pending_approval',
        ]);

        $payment->submitForApproval('finance_transaction', auth()->id());

        $route = $type === 'hutang' ? 'hutang.show' : 'piutang.show';

        return redirect()->route($route, $record->id)
            ->with('success', 'Pembayaran diajukan dan menunggu approval Owner.');
    }

    public function deletePayment(DebtPayment $payment)
    {
        $record = $payment->payable;
        $this->authorizeRecord($record->branch_id);

        if ($payment->isPendingApproval()) {
            return back()->with('error',
                'Pembayaran yang masih menunggu approval tidak bisa dihapus langsung — tolak melalui halaman approval.'
            );
        }

        DB::transaction(function () use ($payment, $record) {
            if ($payment->status === 'approved') {
                $record->decrement('paid_amount', $payment->amount);
                $this->recalcStatus($record);
            }
            $payment->delete();
        });

        $type  = $record instanceof Payable ? 'hutang' : 'piutang';
        $route = $type === 'hutang' ? 'hutang.show' : 'piutang.show';

        return redirect()->route($route, $record->id)
            ->with('success', 'Pembayaran berhasil dihapus.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function recalcStatus(Payable|Receivable $record): void
    {
        $status = match (true) {
            $record->paid_amount >= $record->amount => 'paid',
            $record->paid_amount > 0                => 'partial',
            default                                 => 'unpaid',
        };

        $record->update(['status' => $status]);
    }

    private function buildSummary(string $model, ?int $branchId, bool $isSuperadmin, ?string $filterBranchId): array
    {
        $q = $model::query()
            ->when(!$isSuperadmin, fn ($q) => $q->where('branch_id', $branchId))
            ->when($isSuperadmin && $filterBranchId, fn ($q) => $q->where('branch_id', $filterBranchId));

        return [
            'total'     => (clone $q)->sum('amount'),
            'paid'      => (clone $q)->sum('paid_amount'),
            'remaining' => (clone $q)->selectRaw('SUM(amount - paid_amount) as r')->value('r') ?? 0,
            'overdue'   => (clone $q)->whereNotIn('status', ['paid'])
                               ->whereNotNull('due_date')->where('due_date', '<', today())->count(),
        ];
    }

    private function activeBranchId($user, Request $request): ?int
    {
        if ($user->hasRole('superadmin')) {
            return $request->filled('branch_id') ? (int) $request->branch_id : null;
        }

        $id = $this->branch->idOrNull()
            ?? (int) $user->branches()->orderBy('branches.id')->value('branches.id');

        return $id ?: null;
    }

    private function branchesForUser($user)
    {
        return $user->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : $user->branches()->orderBy('branches.name')->get();
    }

    private function authorizeBranch(int $branchId): void
    {
        $user = auth()->user();
        if ($user->hasRole('superadmin')) return;

        abort_unless(
            $user->branches()->where('branches.id', $branchId)->exists(),
            403, 'Akses cabang tidak diizinkan.'
        );
    }

    private function authorizeRecord(int $branchId): void
    {
        $this->authorizeBranch($branchId);
    }
}
