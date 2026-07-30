<?php

namespace App\Http\Controllers;

use App\Exports\BankStatementExport;
use App\Models\BankAccount;
use App\Models\BankStatementEntry;
use App\Models\Branch;
use App\Services\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class BankReconciliationController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    // ── Bank Accounts ─────────────────────────────────────────────────────────

    public function accountIndex(Request $request)
    {
        $user     = auth()->user();
        $branchId = $this->activeBranchId($user, $request);
        $branches = $this->branchesForUser($user);

        $accounts = BankAccount::with('branch')
            ->when(!$user->hasRole('superadmin'), fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->filled('branch_id') && $user->hasRole('superadmin'),
                fn ($q) => $q->where('branch_id', $request->branch_id))
            ->orderBy('bank_name')
            ->get();

        return view('bank-reconciliation.accounts.index', [
            'accounts'     => $accounts,
            'branches'     => $branches,
            'branchId'     => $branchId,
            'isSuperadmin' => $user->hasRole('superadmin'),
            'filters'      => $request->only(['branch_id']),
        ]);
    }

    public function accountCreate(Request $request)
    {
        $user     = auth()->user();
        $branches = $this->branchesForUser($user);
        $branchId = $this->activeBranchId($user, $request);

        return view('bank-reconciliation.accounts.create', [
            'branches'     => $branches,
            'branchId'     => $branchId,
            'isSuperadmin' => $user->hasRole('superadmin'),
        ]);
    }

    public function accountStore(Request $request)
    {
        $data = $request->validate([
            'branch_id'      => 'required|exists:branches,id',
            'bank_name'      => 'required|string|max:100',
            'account_name'   => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'opening_balance'=> 'required|integer|min:0',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $this->authorizeBranch((int) $data['branch_id']);

        $data['current_balance'] = $data['opening_balance'];

        BankAccount::create($data);

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Rekening bank berhasil ditambahkan.');
    }

    public function accountEdit(BankAccount $bankAccount)
    {
        $this->authorizeBranch($bankAccount->branch_id);

        return view('bank-reconciliation.accounts.edit', [
            'account'      => $bankAccount,
            'isSuperadmin' => auth()->user()->hasRole('superadmin'),
        ]);
    }

    public function accountUpdate(Request $request, BankAccount $bankAccount)
    {
        $this->authorizeBranch($bankAccount->branch_id);

        $data = $request->validate([
            'bank_name'      => 'required|string|max:100',
            'account_name'   => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'is_active'      => 'boolean',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $bankAccount->update($data);

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Rekening bank berhasil diperbarui.');
    }

    public function accountDestroy(BankAccount $bankAccount)
    {
        $this->authorizeBranch($bankAccount->branch_id);

        if ($bankAccount->entries()->exists()) {
            return back()->with('error', 'Rekening tidak dapat dihapus karena sudah memiliki mutasi.');
        }

        $bankAccount->delete();

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Rekening bank berhasil dihapus.');
    }

    // ── Statement Entries (Rekonsiliasi) ──────────────────────────────────────

    public function entryIndex(Request $request, BankAccount $bankAccount)
    {
        $this->authorizeBranch($bankAccount->branch_id);

        $entries = BankStatementEntry::where('bank_account_id', $bankAccount->id)
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->status === 'reconciled') {
                    $q->where('is_reconciled', true);
                } elseif ($request->status === 'unreconciled') {
                    $q->where('is_reconciled', false);
                }
            })
            ->when($request->filled('month'), fn ($q) => $q->whereRaw('DATE_FORMAT(entry_date,"%Y-%m") = ?', [$request->month]))
            ->orderBy('entry_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(30)
            ->withQueryString();

        $summary = $this->buildEntrySummary($bankAccount->id, $request->get('month'));

        return view('bank-reconciliation.entries.index', [
            'account'  => $bankAccount,
            'entries'  => $entries,
            'summary'  => $summary,
            'filters'  => $request->only(['status', 'month']),
        ]);
    }

    public function entryCreate(BankAccount $bankAccount)
    {
        $this->authorizeBranch($bankAccount->branch_id);

        return view('bank-reconciliation.entries.create', ['account' => $bankAccount]);
    }

    public function entryStore(Request $request, BankAccount $bankAccount)
    {
        $this->authorizeBranch($bankAccount->branch_id);

        $data = $request->validate([
            'entry_date'       => 'required|date',
            'description'      => 'required|string|max:255',
            'debit'            => 'nullable|integer|min:0',
            'credit'           => 'nullable|integer|min:0',
            'balance_after'    => 'nullable|integer|min:0',
            'reference_number' => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $data['debit']          = (int) ($data['debit'] ?? 0);
        $data['credit']         = (int) ($data['credit'] ?? 0);
        $data['bank_account_id']= $bankAccount->id;
        $data['created_by']     = auth()->id();

        if ($data['debit'] === 0 && $data['credit'] === 0) {
            return back()->withErrors(['debit' => 'Debit atau kredit harus diisi.'])->withInput();
        }

        DB::transaction(function () use ($data, $bankAccount) {
            BankStatementEntry::create($data);
            // update running balance
            $bankAccount->increment('current_balance', $data['credit'] - $data['debit']);
        });

        return redirect()->route('bank-accounts.entries', $bankAccount->id)
            ->with('success', 'Mutasi berhasil dicatat.');
    }

    public function entryDestroy(BankStatementEntry $entry)
    {
        $this->authorizeBranch($entry->bankAccount->branch_id);

        DB::transaction(function () use ($entry) {
            $entry->bankAccount->decrement('current_balance', $entry->credit - $entry->debit);
            $entry->delete();
        });

        return back()->with('success', 'Mutasi berhasil dihapus.');
    }

    public function entryExport(Request $request, BankAccount $bankAccount)
    {
        $this->authorizeBranch($bankAccount->branch_id);
        $filters = $request->only(['month', 'status']);
        $filename = 'mutasi-bank-'.str_replace(' ', '-', strtolower($bankAccount->account_name)).'-'.now()->format('Ymd').'.xlsx';

        return Excel::download(
            new BankStatementExport($bankAccount->id, $filters, $bankAccount->account_name),
            $filename
        );
    }

    public function reconcile(BankStatementEntry $entry)
    {
        $this->authorizeBranch($entry->bankAccount->branch_id);

        $entry->update([
            'is_reconciled' => !$entry->is_reconciled,
            'reconciled_by' => $entry->is_reconciled ? null : auth()->id(),
            'reconciled_at' => $entry->is_reconciled ? null : now(),
        ]);

        return back()->with('success', $entry->is_reconciled
            ? 'Mutasi ditandai sudah direkonsiliasi.'
            : 'Rekonsiliasi dibatalkan.');
    }

    public function reconcileMany(Request $request, BankAccount $bankAccount)
    {
        $this->authorizeBranch($bankAccount->branch_id);

        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];

        BankStatementEntry::whereIn('id', $ids)
            ->where('bank_account_id', $bankAccount->id)
            ->where('is_reconciled', false)
            ->update([
                'is_reconciled' => true,
                'reconciled_by' => auth()->id(),
                'reconciled_at' => now(),
            ]);

        return back()->with('success', count($ids).' mutasi berhasil direkonsiliasi.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buildEntrySummary(int $accountId, ?string $month): array
    {
        $q = BankStatementEntry::where('bank_account_id', $accountId)
            ->when($month, fn ($q) => $q->whereRaw('DATE_FORMAT(entry_date,"%Y-%m") = ?', [$month]));

        return [
            'total_credit'      => (clone $q)->sum('credit'),
            'total_debit'       => (clone $q)->sum('debit'),
            'unreconciled'      => (clone $q)->where('is_reconciled', false)->count(),
            'reconciled'        => (clone $q)->where('is_reconciled', true)->count(),
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
}
