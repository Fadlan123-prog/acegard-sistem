<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReceiptItem;
use App\Services\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReceiptController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    /** Show the form to record a new goods receipt for a PO. */
    public function create(Purchase $purchase)
    {
        $this->authorizeBranch($purchase);

        if (!$purchase->isReceivable()) {
            return redirect()
                ->route('purchases.show', $purchase->id)
                ->with('error', 'Purchasing ini tidak dapat menerima barang (status: ' . $purchase->status . ').');
        }

        $purchase->load(['items.product', 'supplier', 'branch']);

        // Compute already-received qty per item across all prior receipts
        $receivedQtys = DB::table('purchase_receipt_items as ri')
            ->join('purchase_receipts as r', 'r.id', '=', 'ri.receipt_id')
            ->where('r.purchase_id', $purchase->id)
            ->selectRaw('ri.purchase_item_id, SUM(ri.qty_received) as total_received')
            ->groupBy('ri.purchase_item_id')
            ->pluck('total_received', 'purchase_item_id');

        return view('purchases.receipts.create', compact('purchase', 'receivedQtys'));
    }

    /** Save the goods receipt and update the PO status. */
    public function store(Purchase $purchase, Request $request)
    {
        $this->authorizeBranch($purchase);

        if (!$purchase->isReceivable()) {
            return redirect()
                ->route('purchases.show', $purchase->id)
                ->with('error', 'Purchasing ini tidak dapat menerima barang.');
        }

        $purchase->load('items');

        $itemIds = $purchase->items->pluck('id')->toArray();

        $request->validate([
            'receipt_date'          => ['required', 'date'],
            'notes'                 => ['nullable', 'string', 'max:1000'],
            'items'                 => ['required', 'array'],
            'items.*.purchase_item_id' => ['required', 'integer', 'in:' . implode(',', $itemIds)],
            'items.*.qty_received'  => ['required', 'numeric', 'min:0'],
            'items.*.notes'         => ['nullable', 'string', 'max:500'],
        ]);

        // At least one item must have qty > 0
        $hasQty = collect($request->input('items', []))->contains(
            fn($item) => (float) ($item['qty_received'] ?? 0) > 0
        );

        if (!$hasQty) {
            return back()->withInput()
                ->with('error', 'Minimal satu item harus memiliki jumlah diterima > 0.');
        }

        DB::transaction(function () use ($purchase, $request) {
            $receiptNumber = $this->generateReceiptNumber();

            // Determine receipt completeness: compare total received (all receipts + this one)
            // against each item's ordered qty
            $previouslyReceived = DB::table('purchase_receipt_items as ri')
                ->join('purchase_receipts as r', 'r.id', '=', 'ri.receipt_id')
                ->where('r.purchase_id', $purchase->id)
                ->selectRaw('ri.purchase_item_id, SUM(ri.qty_received) as total')
                ->groupBy('ri.purchase_item_id')
                ->pluck('total', 'purchase_item_id');

            $newQtys = collect($request->input('items', []))
                ->keyBy('purchase_item_id')
                ->map(fn($i) => (float) ($i['qty_received'] ?? 0));

            $allFulfilled = $purchase->items->every(function ($item) use ($previouslyReceived, $newQtys) {
                $alreadyReceived = (float) ($previouslyReceived[$item->id] ?? 0);
                $thisReceipt     = (float) ($newQtys[$item->id] ?? 0);
                return ($alreadyReceived + $thisReceipt) >= (float) $item->qty;
            });

            $receiptStatus = $allFulfilled ? 'complete' : 'partial';

            $receipt = PurchaseReceipt::create([
                'purchase_id'   => $purchase->id,
                'received_by'   => auth()->id(),
                'receipt_number'=> $receiptNumber,
                'receipt_date'  => $request->input('receipt_date'),
                'status'        => $receiptStatus,
                'notes'         => $request->input('notes'),
            ]);

            foreach ($request->input('items', []) as $item) {
                $qty = (float) ($item['qty_received'] ?? 0);
                if ($qty <= 0) continue;

                PurchaseReceiptItem::create([
                    'receipt_id'       => $receipt->id,
                    'purchase_item_id' => (int) $item['purchase_item_id'],
                    'qty_received'     => $qty,
                    'notes'            => $item['notes'] ?? null,
                ]);
            }

            // Advance purchase status
            $newPurchaseStatus = $allFulfilled
                ? Purchase::STATUS_RECEIVED
                : Purchase::STATUS_PARTIAL_RECEIVED;

            $purchase->update(['status' => $newPurchaseStatus]);
        });

        return redirect()
            ->route('purchases.show', $purchase->id)
            ->with('success', 'Penerimaan barang berhasil dicatat.');
    }

    /** View a single goods receipt. */
    public function show(PurchaseReceipt $receipt)
    {
        $receipt->load(['purchase.supplier', 'purchase.branch', 'receiver', 'items.purchaseItem']);

        $this->authorizeBranch($receipt->purchase);

        return view('purchases.receipts.show', compact('receipt'));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function generateReceiptNumber(): string
    {
        $prefix = 'GRN/' . now()->format('Y/m') . '/';

        $last = PurchaseReceipt::where('receipt_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('receipt_number')
            ->value('receipt_number');

        $seq = 1;
        if ($last && preg_match('/\/(\d{4})$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    protected function authorizeBranch(Purchase $purchase): void
    {
        $user = auth()->user();
        if ($user->hasRole('superadmin')) return;

        if ((int) $purchase->branch_id !== $this->branch->id()) {
            abort(403, 'Tidak boleh mengakses purchasing dari cabang lain.');
        }
    }
}
