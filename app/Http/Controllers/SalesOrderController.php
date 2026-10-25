<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Services\BranchContext;
use App\Services\DocumentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SalesOrderController extends Controller
{
    public function __construct(
        private BranchContext $branchContext,
        private DocumentNumberService $docNumbers,
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();

        $marketingScope = function ($q) use ($user) {
            if ($user->hasRole('marketing') && $user->employee) {
                $q->where(fn ($q2) =>
                    $q2->where('marketing_id', $user->employee->id)
                       ->orWhere('created_by', $user->id)
                );
            }
        };

        $query = SalesOrder::accessibleByUser()
            ->tap($marketingScope)
            ->with(['quotation', 'branch', 'marketing'])
            ->latest('date');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('prospect_name', 'like', '%' . $request->search . '%')
                  ->orWhere('so_number', 'like', '%' . $request->search . '%')
                  ->orWhere('prospect_phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('branch_id') && auth()->user()->hasRole('superadmin')) {
            $query->where('branch_id', $request->branch_id);
        }

        $salesOrders = $query->paginate(20)->withQueryString();

        $statusCounts = SalesOrder::accessibleByUser()
            ->tap($marketingScope)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $branches = auth()->user()->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : collect();

        return view('sales-orders.index', compact('salesOrders', 'statusCounts', 'branches'));
    }

    public function create(Request $request)
    {
        $quotation = $request->filled('from_quotation')
            ? Quotation::with('items', 'lead')->find($request->from_quotation)
            : null;

        $lead = $quotation?->lead
            ?? ($request->filled('from_lead') ? Lead::find($request->from_lead) : null);

        $employees = Employee::where('branch_id', $this->branchContext->idOrNull())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $branches = auth()->user()->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : collect();

        return view('sales-orders.create', compact('quotation', 'lead', 'employees', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSalesOrder($request);
        $items     = $this->validateItems($request);

        DB::transaction(function () use ($validated, $items, $request) {
            $branchId = auth()->user()->hasRole('superadmin') && $request->filled('branch_id')
                ? $request->branch_id
                : $this->branchContext->idOrNull();

            $soNumber = $this->docNumbers->generate('sales_orders', 'so_number', 'SO');

            $so = SalesOrder::create([
                ...$validated,
                'branch_id'  => $branchId,
                'so_number'  => $soNumber,
                'created_by' => auth()->id(),
                'subtotal'   => 0,
                'total_amount' => 0,
            ]);

            // Teknisi/kenek/technicians SENGAJA tidak diisi di sini — lihat assign().

            $this->syncItems($so, $items);
            $so->recalculate();

            // Mark quotation as converted
            if ($so->quotation_id) {
                Quotation::where('id', $so->quotation_id)
                    ->whereNull('converted_at')
                    ->update(['converted_at' => now()]);
            }

            // Mark lead as won if linked
            if ($so->lead_id) {
                Lead::where('id', $so->lead_id)
                    ->whereNotIn('status', ['won', 'lost'])
                    ->update(['status' => 'won']);
            }

            session(['_so_id' => $so->id]);
        });

        $id = session('_so_id');
        session()->forget('_so_id');

        return redirect()->route('sales-orders.show', $id)
            ->with('success', 'Sales Order berhasil dibuat.');
    }

    public function show(SalesOrder $salesOrder)
    {
        $this->authorizeAccess($salesOrder);
        $salesOrder->load(['branch', 'quotation', 'lead', 'items', 'tukang', 'kenek', 'marketing', 'technicians', 'creator']);

        $employees = Employee::where('branch_id', $salesOrder->branch_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('sales-orders.show', compact('salesOrder', 'employees'));
    }

    public function edit(SalesOrder $salesOrder)
    {
        $this->authorizeAccess($salesOrder);
        abort_unless($salesOrder->isEditable(), 403, 'Sales Order yang sedang dikerjakan tidak dapat diedit.');

        $salesOrder->load(['items', 'technicians']);
        $employees = Employee::where('branch_id', $salesOrder->branch_id)
            ->where('is_active', true)->orderBy('name')->get();

        return view('sales-orders.edit', compact('salesOrder', 'employees'));
    }

    public function update(Request $request, SalesOrder $salesOrder)
    {
        $this->authorizeAccess($salesOrder);
        abort_unless($salesOrder->isEditable(), 403, 'Sales Order yang sedang dikerjakan tidak dapat diedit.');

        $validated = $this->validateSalesOrder($request);
        $items     = $this->validateItems($request);

        DB::transaction(function () use ($salesOrder, $validated, $items) {
            $salesOrder->update($validated);
            $this->syncItems($salesOrder, $items);
            $salesOrder->recalculate();
        });

        return redirect()->route('sales-orders.show', $salesOrder)
            ->with('success', 'Sales Order berhasil diperbarui.');
    }

    public function destroy(SalesOrder $salesOrder)
    {
        $this->authorizeAccess($salesOrder);
        abort_if($salesOrder->isCompleted(), 403, 'Sales Order yang sudah selesai tidak dapat dihapus.');
        $salesOrder->delete();
        return redirect()->route('sales-orders.index')
            ->with('success', 'Sales Order berhasil dihapus.');
    }

    // ── Status transitions ────────────────────────────────────────────────

    public function confirm(SalesOrder $salesOrder)
    {
        $this->authorizeAccess($salesOrder);
        abort_unless($salesOrder->isPending(), 422, 'Hanya SO dengan status Menunggu yang bisa dikonfirmasi.');

        $salesOrder->update(['status' => 'confirmed', 'confirmed_at' => now()]);

        return back()->with('success', 'Sales Order dikonfirmasi.');
    }

    public function startWork(SalesOrder $salesOrder)
    {
        $this->authorizeAccess($salesOrder);
        abort_unless($salesOrder->isConfirmed(), 422, 'SO harus dikonfirmasi sebelum mulai dikerjakan.');

        $salesOrder->update(['status' => 'in_progress', 'in_progress_at' => now()]);

        return back()->with('success', 'Sales Order ditandai Sedang Dikerjakan.');
    }

    public function complete(SalesOrder $salesOrder)
    {
        $this->authorizeAccess($salesOrder);
        abort_unless($salesOrder->isInProgress(), 422, 'Hanya SO yang sedang dikerjakan yang bisa diselesaikan.');

        if ($salesOrder->assignsMultipleTechnicians()) {
            abort_if($salesOrder->technicians()->count() === 0, 422, 'Tugaskan minimal satu teknisi terlebih dahulu sebelum menyelesaikan SO.');
        } else {
            abort_if(!$salesOrder->tukang_id, 422, 'Tugaskan teknisi terlebih dahulu sebelum menyelesaikan SO.');
        }

        $salesOrder->update(['status' => 'completed', 'completed_at' => now()]);

        return back()->with('success', 'Sales Order selesai! Silakan buat Customer & Invoice.');
    }

    public function cancel(Request $request, SalesOrder $salesOrder)
    {
        $this->authorizeAccess($salesOrder);
        abort_if($salesOrder->isCompleted(), 422, 'Sales Order yang sudah selesai tidak bisa dibatalkan.');
        abort_if($salesOrder->isCancelled(), 422, 'Sales Order sudah dibatalkan.');

        $request->validate(['cancel_reason' => 'nullable|string|max:500']);

        $salesOrder->update([
            'status'        => 'cancelled',
            'cancelled_at'  => now(),
            'cancel_reason' => $request->cancel_reason,
        ]);

        return back()->with('success', 'Sales Order dibatalkan.');
    }

    // ── Penugasan teknisi/kenek ─────────────────────────────────────────────

    /**
     * Tugaskan (atau ganti) teknisi/kenek pada SO. Hanya boleh setelah SO
     * dikonfirmasi — ini satu-satunya jalur penugasan, termasuk untuk
     * reassignment di tengah jalan (confirmed maupun in_progress). Belum ada
     * komisi di tahap ini (dibuat saat pelunasan invoice), jadi tidak perlu
     * logika cancel-komisi seperti WorkOrderController::assign() yang
     * menangani reassignment setelah Customer/Invoice dibuat.
     */
    public function assign(Request $request, SalesOrder $salesOrder)
    {
        $this->authorizeAccess($salesOrder);

        abort_unless(
            in_array($salesOrder->status, ['confirmed', 'in_progress']),
            422,
            'SO harus dikonfirmasi terlebih dahulu sebelum menugaskan teknisi/kenek.'
        );

        if ($salesOrder->assignsMultipleTechnicians()) {
            $data = $request->validate([
                'technicians'   => 'nullable|array',
                'technicians.*' => [
                    'integer',
                    Rule::exists('employees', 'id')->where('branch_id', $salesOrder->branch_id),
                ],
            ]);
            $salesOrder->technicians()->sync($data['technicians'] ?? []);

            return back()->with('success', 'Penugasan teknisi diperbarui.');
        }

        $request->merge([
            'tukang_id' => $request->input('tukang_id') ?: null,
            'kenek_id'  => $request->input('kenek_id') ?: null,
        ]);

        $employeeInBranch = Rule::exists('employees', 'id')->where('branch_id', $salesOrder->branch_id);

        $validated = $request->validate([
            'tukang_id' => ['nullable', 'integer', $employeeInBranch],
            'kenek_id'  => ['nullable', 'integer', $employeeInBranch],
        ]);

        $oldTukangId = $salesOrder->tukang_id ? (int) $salesOrder->tukang_id : null;
        $oldKenekId  = $salesOrder->kenek_id ? (int) $salesOrder->kenek_id : null;
        $newTukangId = $validated['tukang_id'] ? (int) $validated['tukang_id'] : null;
        $newKenekId  = $validated['kenek_id'] ? (int) $validated['kenek_id'] : null;

        $tukangChanged = $oldTukangId !== $newTukangId;
        $kenekChanged  = $oldKenekId !== $newKenekId;

        if (!$tukangChanged && !$kenekChanged) {
            return back()->with('info', 'Penugasan tidak berubah.');
        }

        $salesOrder->update([
            'tukang_id' => $newTukangId,
            'kenek_id'  => $newKenekId,
        ]);

        if ($newTukangId && $tukangChanged) {
            $assignedUser = \App\Models\User::where('employee_id', $newTukangId)->first();
            if ($assignedUser) {
                $salesOrder->load('branch');
                $assignedUser->notify(new \App\Notifications\SalesOrderAssigned($salesOrder));
            }
        }

        $message = $newTukangId
            ? 'Penugasan diperbarui. Teknisi akan melihat pekerjaan ini di dashboard mereka.'
            : 'Teknisi dilepas dari SO ini.';

        return back()->with('success', $message);
    }

    // ── SPK ──────────────────────────────────────────────────────────────

    public function issueSpk(SalesOrder $salesOrder)
    {
        $this->authorizeAccess($salesOrder);
        abort_if($salesOrder->isCancelled(), 422, 'SO yang dibatalkan tidak dapat diterbitkan SPK.');
        abort_if($salesOrder->hasSpk(), 422, 'SPK sudah pernah diterbitkan untuk SO ini.');

        $spkNumber = $this->docNumbers->generate('sales_orders', 'spk_number', 'SPK');

        $salesOrder->update([
            'spk_number'    => $spkNumber,
            'spk_token'     => Str::random(40),
            'spk_issued_at' => now(),
        ]);

        return back()->with('success', "SPK {$spkNumber} berhasil diterbitkan.");
    }

    public function printSpk(SalesOrder $salesOrder)
    {
        $this->authorizeAccess($salesOrder);
        abort_unless($salesOrder->hasSpk(), 404, 'SPK belum diterbitkan untuk SO ini.');

        $salesOrder->load(['branch', 'items', 'tukang', 'kenek', 'marketing', 'technicians', 'quotation', 'creator']);

        return view('sales-orders.spk-print', compact('salesOrder'));
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function authorizeAccess(SalesOrder $salesOrder): void
    {
        $user = auth()->user();
        if ($user->hasRole('superadmin')) return;

        abort_unless(
            $salesOrder->branch_id === $this->branchContext->idOrNull(),
            403,
            'Akses ditolak untuk cabang ini.'
        );
    }

    private function syncItems(SalesOrder $so, array $items): void
    {
        $so->items()->delete();
        foreach ($items as $idx => $item) {
            $qty   = (float) ($item['quantity']        ?? 1);
            $price = (int)   ($item['unit_price']      ?? 0);
            $disc  = (float) ($item['discount_percent'] ?? 0);

            SalesOrderItem::create([
                'sales_order_id'   => $so->id,
                'sort_order'       => $idx,
                'description'      => $item['description'],
                'specifications'   => $item['specifications'] ?? null,
                'unit'             => $item['unit'] ?? 'unit',
                'quantity'         => $qty,
                'unit_price'       => $price,
                'discount_percent' => $disc,
                'subtotal'         => (int) round($qty * $price * (1 - $disc / 100)),
            ]);
        }
    }

    private function validateSalesOrder(Request $request): array
    {
        $validated = $request->validate([
            'lead_id'          => 'nullable|exists:leads,id',
            'quotation_id'     => 'nullable|exists:quotations,id',
            'date'             => 'required|date',
            'scheduled_date'   => 'nullable|date|after_or_equal:date',
            'prospect_name'    => 'required|string|max:255',
            'prospect_phone'   => 'nullable|string|max:30',
            'prospect_email'   => 'nullable|email|max:255',
            'prospect_address' => 'nullable|string|max:500',
            'category'         => 'required|in:car_film,building_film,ppf',
            'vehicle_brand'    => 'nullable|string|max:100',
            'vehicle_model'    => 'nullable|string|max:100',
            'vehicle_year'     => 'nullable|string|max:10',
            'vehicle_plate'    => 'nullable|string|max:20',
            'marketing_id'     => 'nullable|exists:employees,id',
            'discount_amount'  => 'nullable|integer|min:0',
            'notes'            => 'nullable|string|max:2000',
        ]);

        return $validated;
    }

    private function validateItems(Request $request): array
    {
        $request->validate([
            'items'                    => 'required|array|min:1',
            'items.*.description'      => 'required|string|max:255',
            'items.*.specifications'   => 'nullable|string|max:255',
            'items.*.unit'             => 'nullable|string|max:20',
            'items.*.quantity'         => 'required|numeric|min:0.01',
            'items.*.unit_price'       => 'required|integer|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        return $request->input('items', []);
    }
}
