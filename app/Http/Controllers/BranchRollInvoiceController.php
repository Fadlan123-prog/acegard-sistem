<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchRollInvoice;
use App\Services\BranchContext;
use App\Services\BranchRollSaleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Invoice penjualan roll antar cabang. Cabang penjual membuat invoice dan membatalkannya,
 * cabang pembeli menerima roll, penjual menandai lunas. superadmin dan owner lintas cabang.
 */
class BranchRollInvoiceController extends Controller
{
    public function __construct(private BranchRollSaleService $sales) {}

    /** Diambil saat dipakai (bukan lewat constructor) supaya selalu cabang aktif request yang sedang berjalan. */
    private function branch(): BranchContext
    {
        return app(BranchContext::class);
    }

    private function crossBranch(): bool
    {
        return auth()->user()->hasAnyRole(['superadmin', 'owner']);
    }

    private function abortUnlessInvolved(BranchRollInvoice $invoice): void
    {
        if ($this->crossBranch()) {
            return;
        }

        $id = $this->branch()->id();
        abort_unless((int) $invoice->from_branch_id === $id || (int) $invoice->to_branch_id === $id, 403);
    }

    private function abortUnlessSeller(BranchRollInvoice $invoice): void
    {
        abort_unless($this->crossBranch() || (int) $invoice->from_branch_id === $this->branch()->id(), 403);
    }

    private function abortUnlessBuyer(BranchRollInvoice $invoice): void
    {
        abort_unless($this->crossBranch() || (int) $invoice->to_branch_id === $this->branch()->id(), 403);
    }

    public function index(Request $request)
    {
        $query = BranchRollInvoice::query();

        if (!$this->crossBranch()) {
            $id = $this->branch()->id();
            $query->where(fn ($q) => $q->where('from_branch_id', $id)->orWhere('to_branch_id', $id));
        }

        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('delivery_status')) {
            $query->where('delivery_status', $request->delivery_status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $totals = (clone $query)->where('delivery_status', '!=', 'cancelled')->selectRaw('
            COUNT(*) as total_count,
            COALESCE(SUM(total_amount), 0) as total_amount,
            COALESCE(SUM(CASE WHEN payment_status = "unpaid" THEN total_amount ELSE 0 END), 0) as total_unpaid
        ')->first();

        $invoices = (clone $query)->with(['fromBranch', 'toBranch'])->withCount('items')->latest('invoice_date')->latest('id')->paginate(15)->withQueryString();

        return view('branch-roll-invoices.index', [
            'invoices'   => $invoices,
            'totals'     => $totals,
            'activeId'   => $this->branch()->id(),
            'crossBranch' => $this->crossBranch(),
        ]);
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $fromId = $this->branch()->id();

        return view('branch-roll-invoices.create', [
            'branches'    => $branches,
            'fromId'      => $fromId,
            'crossBranch' => $this->crossBranch(),
            'pools'       => $this->sales->availablePools($fromId),
        ]);
    }

    /** Daftar stok roll utuh sebuah cabang (untuk form create). */
    public function pools(Request $request)
    {
        $branchId = (int) $request->query('branch_id', $this->branch()->id());

        if (!$this->crossBranch()) {
            abort_unless($branchId === $this->branch()->id(), 403);
        }

        return response()->json($this->sales->availablePools($branchId)->map(fn ($p) => [
            'id'        => $p->id,
            'label'     => trim("{$p->film_type} {$p->darkness_code}"),
            'available' => $p->available_rolls,
            'price'     => $p->roll_price,
            'unit'      => $p->roll_unit,
        ])->values());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'from_branch_id' => ['nullable', 'exists:branches,id'],
            'to_branch_id'   => ['required', 'exists:branches,id'],
            'invoice_date'   => ['required', 'date'],
            'due_date'       => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'notes'          => ['nullable', 'string', 'max:2000'],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.film_stock_pool_id' => ['required', 'integer', 'exists:film_stock_pools,id'],
            'items.*.qty'    => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        // Cabang penjual selalu cabang aktif, kecuali superadmin/owner yang boleh memilih.
        $data['from_branch_id'] = $this->crossBranch() && !empty($data['from_branch_id'])
            ? (int) $data['from_branch_id']
            : $this->branch()->id();

        try {
            $invoice = $this->sales->create($data, auth()->id());
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        return redirect()->route('branch-roll-invoices.show', $invoice)->with('success', 'Invoice roll antar cabang berhasil dibuat. Stok penjual sudah berkurang.');
    }

    public function show(BranchRollInvoice $invoice)
    {
        $this->abortUnlessInvolved($invoice);
        $invoice->load(['fromBranch', 'toBranch', 'creator', 'receiver', 'items.rolls']);

        $id = $this->branch()->id();

        return view('branch-roll-invoices.show', [
            'invoice'   => $invoice,
            'isSeller'  => $this->crossBranch() || (int) $invoice->from_branch_id === $id,
            'isBuyer'   => $this->crossBranch() || (int) $invoice->to_branch_id === $id,
        ]);
    }

    public function receive(BranchRollInvoice $invoice)
    {
        $this->abortUnlessBuyer($invoice);

        try {
            $this->sales->receive($invoice, auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Roll diterima dan sudah masuk ke stok cabang.');
    }

    public function cancel(BranchRollInvoice $invoice)
    {
        $this->abortUnlessSeller($invoice);

        try {
            $this->sales->cancel($invoice);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Invoice dibatalkan. Roll kembali ke stok penjual.');
    }

    public function markPaid(BranchRollInvoice $invoice)
    {
        $this->abortUnlessSeller($invoice);

        try {
            $this->sales->markPaid($invoice);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Invoice ditandai lunas.');
    }

    public function pdf(BranchRollInvoice $invoice)
    {
        $this->abortUnlessInvolved($invoice);
        $invoice->load(['fromBranch', 'toBranch', 'creator', 'items.rolls']);

        return Pdf::loadView('branch-roll-invoices.pdf', ['invoice' => $invoice])
            ->setPaper('a4')
            ->stream($invoice->invoice_number . '.pdf');
    }
}
