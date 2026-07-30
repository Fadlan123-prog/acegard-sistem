<?php

namespace App\Http\Controllers;

use App\Exports\SupportingGoodExport;
use App\Models\Branch;
use App\Models\SupportingGood;
use App\Models\SupportingGoodMutation;
use App\Services\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SupportingGoodController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function index(Request $request)
    {
        $user     = auth()->user();
        $branchId = $this->activeBranchId($user, $request);
        $branches = $this->branchesForUser($user);

        $query = SupportingGood::with('branch')
            ->when(!$user->hasRole('superadmin'), fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->filled('branch_id') && $user->hasRole('superadmin'),
                fn ($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->filled('category'),
                fn ($q) => $q->where('category', $request->category))
            ->when($request->filled('search'),
                fn ($q) => $q->where('name', 'like', '%'.$request->search.'%'))
            ->orderBy('category')
            ->orderBy('name');

        $goods = $query->paginate(20)->withQueryString();

        $lowStockCount = (clone $query->getQuery())->whereColumn('current_stock', '<=', 'minimum_stock')
            ->where('minimum_stock', '>', 0)->count();

        return view('supporting-goods.index', [
            'goods'        => $goods,
            'branches'     => $branches,
            'branchId'     => $branchId,
            'isSuperadmin' => $user->hasRole('superadmin'),
            'categories'   => SupportingGood::$categories,
            'lowStockCount'=> $lowStockCount,
            'filters'      => $request->only(['search', 'category', 'branch_id']),
        ]);
    }

    public function create(Request $request)
    {
        $user     = auth()->user();
        $branches = $this->branchesForUser($user);
        $branchId = $this->activeBranchId($user, $request);

        return view('supporting-goods.create', [
            'branches'     => $branches,
            'branchId'     => $branchId,
            'isSuperadmin' => $user->hasRole('superadmin'),
            'categories'   => SupportingGood::$categories,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id'     => 'required|exists:branches,id',
            'name'          => 'required|string|max:255',
            'category'      => 'required|in:'.implode(',', array_keys(SupportingGood::$categories)),
            'unit'          => 'required|string|max:30',
            'minimum_stock' => 'required|numeric|min:0',
            'description'   => 'nullable|string|max:1000',
        ]);

        $this->authorizeBranch((int) $data['branch_id']);

        $data['current_stock'] = 0;

        SupportingGood::create($data);

        return redirect()->route('supporting-goods.index')
            ->with('success', "Barang \"{$data['name']}\" berhasil ditambahkan.");
    }

    public function edit(SupportingGood $supportingGood)
    {
        $this->authorizeGoodAccess($supportingGood);

        $user     = auth()->user();
        $branches = $this->branchesForUser($user);

        return view('supporting-goods.edit', [
            'good'         => $supportingGood,
            'branches'     => $branches,
            'isSuperadmin' => $user->hasRole('superadmin'),
            'categories'   => SupportingGood::$categories,
        ]);
    }

    public function update(Request $request, SupportingGood $supportingGood)
    {
        $this->authorizeGoodAccess($supportingGood);

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'category'      => 'required|in:'.implode(',', array_keys(SupportingGood::$categories)),
            'unit'          => 'required|string|max:30',
            'minimum_stock' => 'required|numeric|min:0',
            'description'   => 'nullable|string|max:1000',
        ]);

        $supportingGood->update($data);

        return redirect()->route('supporting-goods.index')
            ->with('success', "Barang \"{$supportingGood->name}\" berhasil diperbarui.");
    }

    public function destroy(SupportingGood $supportingGood)
    {
        $this->authorizeGoodAccess($supportingGood);

        if ($supportingGood->mutations()->exists()) {
            return back()->with('error', 'Barang tidak dapat dihapus karena sudah memiliki riwayat mutasi.');
        }

        $name = $supportingGood->name;
        $supportingGood->delete();

        return redirect()->route('supporting-goods.index')
            ->with('success', "Barang \"{$name}\" berhasil dihapus.");
    }

    public function mutate(Request $request, SupportingGood $supportingGood)
    {
        $this->authorizeGoodAccess($supportingGood);

        $data = $request->validate([
            'type'     => 'required|in:in,out,opname',
            'quantity' => 'required|numeric|min:0.01',
            'notes'    => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($data, $supportingGood) {
                $good = SupportingGood::whereKey($supportingGood->id)->lockForUpdate()->firstOrFail();

                $stockBefore = (float) $good->current_stock;

                if ($data['type'] === 'out' && (float) $data['quantity'] > $stockBefore) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'quantity' => "Stok tidak cukup. Sisa stok saat ini: {$stockBefore} {$good->unit}.",
                    ]);
                }

                $stockAfter = match ($data['type']) {
                    'in'     => $stockBefore + (float) $data['quantity'],
                    'out'    => $stockBefore - (float) $data['quantity'],
                    'opname' => (float) $data['quantity'],
                };

                SupportingGoodMutation::create([
                    'supporting_good_id' => $good->id,
                    'branch_id'          => $good->branch_id,
                    'type'               => $data['type'],
                    'quantity'           => $data['quantity'],
                    'stock_before'       => $stockBefore,
                    'stock_after'        => $stockAfter,
                    'notes'              => $data['notes'] ?? null,
                    'recorded_by'        => auth()->id(),
                ]);

                $good->update(['current_stock' => $stockAfter]);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $label = match ($data['type']) {
            'in'     => 'Barang masuk berhasil dicatat.',
            'out'    => 'Pemakaian berhasil dicatat.',
            'opname' => 'Stock opname berhasil disimpan.',
        };

        return redirect()->route('supporting-goods.index',
            request()->only(['search', 'category', 'branch_id']))
            ->with('success', $label);
    }

    public function history(SupportingGood $supportingGood)
    {
        $this->authorizeGoodAccess($supportingGood);

        $mutations = $supportingGood->mutations()
            ->with('recorder')
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('supporting-goods.history', [
            'good'      => $supportingGood->load('branch'),
            'mutations' => $mutations,
        ]);
    }

    public function export(Request $request)
    {
        $user     = auth()->user();
        $branchId = null;
        if (!$user->hasRole('superadmin')) {
            $branchId = $this->branch->idOrNull()
                ?? (int) $user->branches()->orderBy('branches.id')->value('branches.id') ?: null;
        } elseif ($request->filled('branch_id')) {
            $branchId = (int) $request->branch_id;
        }

        $filters = array_filter(['branch_id' => $branchId, 'category' => $request->category]);

        return Excel::download(new SupportingGoodExport($filters), 'barang-pendukung-'.now()->format('Ymd').'.xlsx');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function activeBranchId($user, Request $request): ?int
    {
        if ($user->hasRole('superadmin')) {
            return $request->filled('branch_id') ? (int) $request->branch_id : null;
        }

        $id = $this->branch->idOrNull();

        if (!$id) {
            $id = (int) $user->branches()->orderBy('branches.id')->value('branches.id');
        }

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

        if ($user->hasRole('superadmin')) {
            return;
        }

        abort_unless(
            $user->branches()->where('branches.id', $branchId)->exists(),
            403,
            'Akses cabang tidak diizinkan.'
        );
    }

    private function authorizeGoodAccess(SupportingGood $good): void
    {
        $this->authorizeBranch($good->branch_id);
    }
}
