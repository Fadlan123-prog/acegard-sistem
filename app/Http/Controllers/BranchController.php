<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Services\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function switch(Request $request)
    {
        $validated = $request->validate(
            [
                'branch_id' => ['required', 'integer', 'exists:branches,id'],
            ],
            [
                'branch_id.required' => 'Cabang wajib dipilih.',
                'branch_id.exists'   => 'Cabang yang dipilih tidak valid.',
            ]
        );

        $user     = $request->user();
        $branchId = (int) $validated['branch_id'];

        if (! $user->hasRole('superadmin')) {
            $hasAccess = $user->branches()
                ->where('branches.id', $branchId)
                ->exists();

            if (! $hasAccess) {
                abort(403, 'Tidak boleh pilih cabang lain.');
            }
        }

        $this->branch->switchTo($branchId);

        return back()->with('success', 'Cabang aktif berhasil diubah.');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->get('search'));

        $branches = Branch::query()
            ->withCount([
                'customers',
                'customerBuildings',
                'employees',
            ])
            ->when(! $request->user()->hasRole('superadmin'), fn ($q) => $q->whereIn('id', $request->user()->accessibleBranchIds()))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('branches.index', [
            'branches' => $branches,
            'search'   => $search,
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $this->requireSuperadmin();

        $ids = collect(explode(',', (string) $request->input('selected_ids')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Tidak ada cabang yang dipilih.');
        }

        $branches = Branch::query()
            ->withCount([
                'customers',
                'customerBuildings',
                'employees',
            ])
            ->whereIn('id', $ids)
            ->get();

        if ($branches->isEmpty()) {
            return back()->with('error', 'Cabang yang dipilih tidak ditemukan.');
        }

        $branchesInUse = $branches
            ->filter(fn ($branch) => $this->branchHasRelatedData($branch))
            ->pluck('name')
            ->values();

        if ($branchesInUse->isNotEmpty()) {
            return back()->with(
                'error',
                'Cabang tidak bisa dihapus karena masih memiliki data customer, customer building, atau employee: ' .
                $branchesInUse->implode(', ')
            );
        }

        Branch::whereIn('id', $branches->pluck('id'))->delete();

        if ($ids->contains($this->branch->idOrNull())) {
            $this->branch->flush();
        }

        return redirect()
            ->route('branches.index')
            ->with('success', 'Cabang terpilih berhasil dihapus.');
    }

    public function create()
    {
        $this->requireSuperadmin();

        return view('branches.create', [
            'title'     => 'Tambah Cabang',
            'employees' => Employee::orderBy('name')->get(['id', 'name', 'job_position']),
        ]);
    }

    public function store(Request $request)
    {
        $this->requireSuperadmin();

        $validated = $this->validateBranch($request);

        $branch = Branch::create($validated);

        return redirect()
            ->route('users.create', ['branch_id' => $branch->id])
            ->with('success', 'Cabang berhasil dibuat. Silakan tambah user untuk cabang ini.');
    }

    public function edit(Branch $branch)
    {
        $this->authorizeBranch($branch);

        return view('branches.edit', [
            'title'     => 'Edit Cabang',
            'branch'    => $branch,
            'employees' => Employee::orderBy('name')->get(['id', 'name', 'job_position']),
        ]);
    }

    public function update(Request $request, Branch $branch)
    {
        $this->authorizeBranch($branch);

        $validated = $this->validateBranch($request, $branch->id);

        $branch->update($validated);

        // Flush the Redis/in-process cache so the updated name propagates immediately
        if ($this->branch->idOrNull() === (int) $branch->id) {
            $this->branch->flush();
        }

        return redirect()
            ->route('branches.index')
            ->with('success', 'Cabang berhasil diupdate.');
    }

    public function destroy(Branch $branch)
    {
        $this->requireSuperadmin();

        $branch->loadCount([
            'customers',
            'customerBuildings',
            'employees',
            'invoiceBuildings',
        ]);

        if ($this->branchHasRelatedData($branch)) {
            return back()->with(
                'error',
                'Cabang tidak bisa dihapus karena masih memiliki data customer, customer building, atau employee.'
            );
        }

        if ($this->branch->idOrNull() === (int) $branch->id) {
            $this->branch->flush();
        }

        $branch->delete();

        return redirect()
            ->route('branches.index')
            ->with('success', 'Cabang berhasil dihapus.');
    }

    private function requireSuperadmin(): void
    {
        abort_unless(auth()->user()->hasRole('superadmin'), 403, 'Hanya superadmin yang dapat menambah/menghapus cabang.');
    }

    private function authorizeBranch(Branch $branch): void
    {
        abort_unless(auth()->user()->canAccessBranch((int) $branch->id), 403, 'Bukan cabang Anda.');
    }

    private function validateBranch(Request $request, ?int $branchId = null): array
    {
        return $request->validate(
            [
                'name'         => ['required', 'string', 'max:255'],
                'code'         => ['nullable', 'string', 'max:30', Rule::unique('branches', 'code')->ignore($branchId)],
                'type'         => ['nullable', Rule::in(['head_office', 'branch', 'warehouse', 'service_center'])],
                'status'       => ['required', Rule::in(['active', 'inactive'])],
                'city'         => ['required', 'string', 'max:255'],
                'province'     => ['nullable', 'string', 'max:150'],
                'district'     => ['nullable', 'string', 'max:150'],
                'postal_code'  => ['nullable', 'string', 'max:10'],
                'address'      => ['required', 'string'],
                'phone'        => ['nullable', 'string', 'max:30'],
                'whatsapp'     => ['nullable', 'string', 'max:30'],
                'email'        => ['nullable', 'email', 'max:255'],
                'opening_time' => ['nullable', 'date_format:H:i'],
                'closing_time' => ['nullable', 'date_format:H:i'],
                'working_days' => ['nullable', 'array'],
                'working_days.*' => ['string'],
                'manager_id'   => ['nullable', 'exists:employees,id'],
                'pic_id'       => ['nullable', 'exists:employees,id'],
                'opening_date' => ['nullable', 'date'],
                'notes'        => ['nullable', 'string'],
            ],
            [
                'name.required'    => 'Nama cabang wajib diisi.',
                'city.required'    => 'Kota wajib diisi.',
                'address.required' => 'Alamat wajib diisi.',
                'status.required'  => 'Status cabang wajib dipilih.',
                'code.unique'      => 'Kode cabang sudah digunakan cabang lain.',
                'email.email'      => 'Format email tidak valid.',
                'opening_time.date_format' => 'Format jam buka tidak valid (HH:MM).',
                'closing_time.date_format' => 'Format jam tutup tidak valid (HH:MM).',
                'manager_id.exists' => 'Manager yang dipilih tidak valid.',
                'pic_id.exists'     => 'PIC yang dipilih tidak valid.',
            ]
        );
    }

    public function resetActiveBranch()
    {
        session()->forget('active_branch_id');

        return redirect()->route('dashboard')->with('success', 'Active branch berhasil direset.');
    }

    private function branchHasRelatedData(Branch $branch): bool
    {
        // customer_ppf and bank_accounts cascade-delete on branch removal
        // (see their migrations), so without this check a branch with no
        // customers/employees but real PPF or bank-reconciliation history
        // could still be deleted, silently wiping that data (audit finding
        // M-15). payrolls/purchases don't cascade but would be orphaned.
        return
            (int) ($branch->customers_count ?? 0) > 0 ||
            (int) ($branch->customer_buildings_count ?? 0) > 0 ||
            (int) ($branch->employees_count ?? 0) > 0 ||
            (int) ($branch->invoice_buildings_count ?? 0) > 0 ||
            DB::table('customer_ppf')->where('branch_id', $branch->id)->exists() ||
            DB::table('bank_accounts')->where('branch_id', $branch->id)->exists() ||
            DB::table('payrolls')->where('branch_id', $branch->id)->exists() ||
            DB::table('purchases')->where('branch_id', $branch->id)->exists();
    }
}
