<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Services\BranchContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Purchase::with(['branch', 'supplier', 'user']);

        if (!$user->hasRole('superadmin')) {
            $query->where('branch_id', $this->branch->id());
        } else {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('purchase_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('purchase_date', '<=', $request->to);
        }

        // Aggregate totals on the full filtered dataset — before pagination
        $totals = (clone $query)->selectRaw('
            COUNT(*) as total_count,
            SUM(total_amount) as total_amount,
            SUM((SELECT COUNT(*) FROM purchase_items pi WHERE pi.purchase_id = purchases.id AND pi.deleted_at IS NULL)) as total_items
        ')->first();

        $purchases = (clone $query)
            ->withCount('items')
            ->latest('purchase_date')
            ->paginate(15)
            ->withQueryString();

        $branches  = Branch::orderBy('name')->get();
        $suppliers = \App\Models\Supplier::orderBy('name')->get(['id', 'name']);

        return view('purchases.index', compact(
            'purchases', 'branches', 'suppliers', 'totals'
        ));
    }

    public function create()
    {
        $user = auth()->user();

        $branches = $user->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : $user->branches()->orderBy('name')->get();

        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $activeBranchId = $this->branch->idOrNull();

        return view('purchases.create', compact(
            'branches',
            'suppliers',
            'products',
            'activeBranchId'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'purchase_number' => ['nullable', 'string', 'max:255', 'unique:purchases,purchase_number'],
            'purchase_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['draft', 'confirmed', 'sent', 'cancelled'])],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $user = auth()->user();

        if (!$user->hasRole('superadmin')) {
            if (!$user->branches->pluck('id')->contains((int) $data['branch_id'])) {
                abort(403, 'Tidak boleh input purchasing untuk cabang lain.');
            }
        }

        DB::beginTransaction();

        try {
            $purchaseNumber = !empty($data['purchase_number'])
                ? $data['purchase_number']
                : $this->generatePurchaseNumber();

            $totalAmount = collect($data['items'])->sum(function ($item) {
                return (float) $item['qty'] * (float) $item['unit_price'];
            });

            $purchase = Purchase::create([
                'branch_id' => $data['branch_id'],
                'supplier_id' => $data['supplier_id'] ?? null,
                'user_id' => auth()->id(),
                'purchase_number' => $purchaseNumber,
                'purchase_date' => $this->normalizePurchaseDate($data['purchase_date']),
                'total_amount' => $totalAmount,
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $qty = (float) $item['qty'];
                $unitPrice = (float) $item['unit_price'];
                $totalPrice = $qty * $unitPrice;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'qty' => $qty,
                    'unit' => $item['unit'] ?? null,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('purchases.index')
                ->with('success', 'Data purchasing berhasil disimpan.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Gagal menyimpan purchasing: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Purchase $purchase)
    {
        $this->authorizeBranch($purchase);

        $purchase->load([
            'branch',
            'supplier',
            'user',
            'items.product',
            'receipts.receiver',
        ]);

        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $this->authorizeBranch($purchase);

        $purchase->load('items');

        $user = auth()->user();

        $branches = $user->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : $user->branches()->orderBy('name')->get();

        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('purchases.edit', compact(
            'purchase',
            'branches',
            'suppliers',
            'products'
        ));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $this->authorizeBranch($purchase);

        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'purchase_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('purchases', 'purchase_number')->ignore($purchase->id),
            ],
            'purchase_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['draft', 'confirmed', 'sent', 'cancelled'])],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        // Status transition guard
        $currentStatus = $purchase->status;
        $newStatus     = $data['status'];

        if ($currentStatus === 'cancelled') {
            return back()
                ->with('error', 'Purchasing yang sudah Cancelled tidak bisa diubah.')
                ->withInput();
        }

        // GRN-managed statuses cannot be manually overridden via edit
        if (in_array($currentStatus, ['partial_received', 'received', 'matched'])) {
            return back()
                ->with('error', 'Status sudah dikelola oleh proses penerimaan barang dan tidak bisa diubah manual.')
                ->withInput();
        }

        if (in_array($currentStatus, ['confirmed', 'sent']) && $newStatus === 'draft') {
            return back()
                ->with('error', 'Purchasing yang sudah Confirmed/Sent tidak bisa dikembalikan ke Draft.')
                ->withInput();
        }

        $user = auth()->user();

        if (!$user->hasRole('superadmin')) {
            if (!$user->branches->pluck('id')->contains((int) $data['branch_id'])) {
                abort(403, 'Tidak boleh memindahkan purchasing ke cabang lain.');
            }
        }

        DB::beginTransaction();

        try {
            $purchaseNumber = !empty($data['purchase_number'])
                ? $data['purchase_number']
                : $purchase->purchase_number;

            if (empty($purchaseNumber)) {
                $purchaseNumber = $this->generatePurchaseNumber();
            }

            $totalAmount = collect($data['items'])->sum(function ($item) {
                return (float) $item['qty'] * (float) $item['unit_price'];
            });

            $purchase->update([
                'branch_id' => $data['branch_id'],
                'supplier_id' => $data['supplier_id'] ?? null,
                'purchase_number' => $purchaseNumber,
                'purchase_date' => $this->normalizePurchaseDate($data['purchase_date']),
                'total_amount' => $totalAmount,
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);

            $purchase->items()->delete();

            foreach ($data['items'] as $item) {
                $qty = (float) $item['qty'];
                $unitPrice = (float) $item['unit_price'];
                $totalPrice = $qty * $unitPrice;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'qty' => $qty,
                    'unit' => $item['unit'] ?? null,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('purchases.index')
                ->with('success', 'Data purchasing berhasil diperbarui.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Gagal memperbarui purchasing: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Purchase $purchase)
    {
        $this->authorizeBranch($purchase);

        if ($purchase->status !== 'draft') {
            return back()->with('error',
                'Hanya purchasing berstatus Draft yang bisa dihapus. ' .
                'Ubah status ke Cancelled terlebih dahulu jika ingin membatalkan.'
            );
        }

        DB::beginTransaction();

        try {
            $purchase->items()->delete();
            $purchase->delete();

            DB::commit();

            return redirect()
                ->route('purchases.index')
                ->with('success', 'Data purchasing berhasil dihapus.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Gagal menghapus purchasing: ' . $e->getMessage());
        }
    }

    protected function generatePurchaseNumber(): string
    {
        $prefix = 'PO/' . now()->format('Y/m') . '/';

        $last = Purchase::where('purchase_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('purchase_number')
            ->value('purchase_number');

        $seq = 1;
        if ($last && preg_match('/\/(\d{4})$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    protected function authorizeBranch(Purchase $purchase): void
    {
        $user = auth()->user();

        if ($user->hasRole('superadmin')) {
            return;
        }

        $activeBranchId = $this->branch->id();

        if ((int) $purchase->branch_id !== $activeBranchId) {
            abort(403, 'Tidak boleh mengakses purchasing dari cabang lain.');
        }
    }

    protected function normalizePurchaseDate(string $date): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d');
        }

        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
            return \Carbon\Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
            return \Carbon\Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        }

        return \Carbon\Carbon::parse($date)->format('Y-m-d');
    }
}
