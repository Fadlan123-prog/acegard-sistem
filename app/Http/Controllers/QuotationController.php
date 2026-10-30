<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Services\BranchContext;
use App\Services\DocumentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
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
                    $q2->where('assigned_to', $user->employee->id)
                       ->orWhere('created_by', $user->id)
                );
            }
        };

        $query = Quotation::accessibleByUser()
            ->tap($marketingScope)
            ->with(['lead', 'assignee', 'branch'])
            ->latest('date');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('prospect_name', 'like', '%' . $request->search . '%')
                  ->orWhere('quotation_number', 'like', '%' . $request->search . '%')
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

        $quotations = $query->paginate(20)->withQueryString();

        $statusCounts = Quotation::accessibleByUser()
            ->tap($marketingScope)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $branches = auth()->user()->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : collect();

        return view('quotations.index', compact('quotations', 'statusCounts', 'branches'));
    }

    public function create(Request $request)
    {
        $lead = $request->filled('from_lead')
            ? Lead::find($request->from_lead)
            : null;

        $employees = Employee::where('branch_id', $this->branchContext->idOrNull())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $branches = auth()->user()->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : collect();

        return view('quotations.create', compact('lead', 'employees', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateQuotation($request);
        $items     = $this->validateItems($request);

        DB::transaction(function () use ($validated, $items, $request) {
            $branchId = auth()->user()->hasRole('superadmin') && $request->filled('branch_id')
                ? $request->branch_id
                : $this->branchContext->idOrNull();

            $quotationNumber = $this->docNumbers->generate('quotations', 'quotation_number', 'QT');

            $quotation = Quotation::create([
                ...$validated,
                'branch_id'        => $branchId,
                'quotation_number' => $quotationNumber,
                'created_by'       => auth()->id(),
                'subtotal'         => 0,
                'total_amount'     => 0,
            ]);

            $this->syncItems($quotation, $items);
            $quotation->recalculate();

            // Mark lead as quoted
            if ($quotation->lead_id) {
                $lead = Lead::find($quotation->lead_id);
                if ($lead && !in_array($lead->status, ['won', 'lost', 'quoted'])) {
                    $lead->update(['status' => 'quoted', 'quoted_at' => now()]);
                }
            }

            session(['_quotation_id' => $quotation->id]);
        });

        $id = session('_quotation_id');
        session()->forget('_quotation_id');

        return redirect()->route('quotations.show', $id)
            ->with('success', 'Quotation berhasil dibuat.');
    }

    public function show(Quotation $quotation)
    {
        $this->authorizeAccess($quotation);
        $quotation->load(['branch', 'lead', 'assignee', 'creator', 'items']);
        return view('quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        $this->authorizeAccess($quotation);
        abort_unless($quotation->isEditable(), 403, 'Quotation yang sudah terkirim tidak dapat diedit.');

        $quotation->load('items');
        $employees = Employee::where('branch_id', $quotation->branch_id)
            ->where('is_active', true)->orderBy('name')->get();

        return view('quotations.edit', compact('quotation', 'employees'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        $this->authorizeAccess($quotation);
        abort_unless($quotation->isEditable(), 403, 'Quotation yang sudah terkirim tidak dapat diedit.');

        $validated = $this->validateQuotation($request);
        $items     = $this->validateItems($request);

        DB::transaction(function () use ($quotation, $validated, $items) {
            $quotation->update($validated);
            $this->syncItems($quotation, $items);
            $quotation->recalculate();
        });

        return redirect()->route('quotations.show', $quotation)
            ->with('success', 'Quotation berhasil diperbarui.');
    }

    public function destroy(Quotation $quotation)
    {
        $this->authorizeAccess($quotation);
        $quotation->delete();
        return redirect()->route('quotations.index')
            ->with('success', 'Quotation berhasil dihapus.');
    }

    public function send(Quotation $quotation)
    {
        $this->authorizeAccess($quotation);
        abort_unless($quotation->isDraft(), 422, 'Hanya draft yang bisa dikirim.');
        abort_if($quotation->items()->count() === 0, 422, 'Tambahkan minimal 1 item sebelum mengirim.');

        $quotation->update(['status' => 'sent', 'sent_at' => now()]);

        return back()->with('success', 'Quotation ditandai sebagai Terkirim.');
    }

    public function accept(Quotation $quotation)
    {
        $this->authorizeAccess($quotation);
        abort_unless($quotation->isSent(), 422, 'Hanya quotation terkirim yang bisa diterima.');

        $quotation->update(['status' => 'accepted', 'accepted_at' => now()]);

        // Update lead status to won if linked
        if ($quotation->lead_id) {
            Lead::where('id', $quotation->lead_id)
                ->whereNotIn('status', ['won'])
                ->update(['status' => 'won']);
        }

        return redirect()
            ->route('sales-orders.create', ['from_quotation' => $quotation->id])
            ->with('success', 'Quotation diterima! Buat Sales Order untuk melanjutkan.');
    }

    public function reject(Request $request, Quotation $quotation)
    {
        $this->authorizeAccess($quotation);
        abort_unless($quotation->isSent(), 422, 'Hanya quotation terkirim yang bisa ditolak.');

        $request->validate(['rejection_reason' => 'nullable|string|max:500']);

        $quotation->update([
            'status'           => 'rejected',
            'rejected_at'      => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Update lead status to lost if linked
        if ($quotation->lead_id) {
            Lead::where('id', $quotation->lead_id)
                ->whereNotIn('status', ['won', 'lost'])
                ->update([
                    'status'      => 'lost',
                    'lost_reason' => $request->rejection_reason ?? 'Quotation ditolak.',
                ]);
        }

        return back()->with('success', 'Quotation ditandai sebagai Ditolak.');
    }

    public function print(Quotation $quotation)
    {
        $this->authorizeAccess($quotation);
        $quotation->load(['branch', 'lead', 'assignee', 'items']);
        return view('quotations.print', compact('quotation'));
    }

    private function validateQuotation(Request $request): array
    {
        $validated = $request->validate([
            'lead_id'          => 'nullable|exists:leads,id',
            'date'             => 'required|date',
            'valid_until'      => 'required|date|after_or_equal:date',
            'prospect_name'    => 'required|string|max:255',
            'prospect_phone'   => 'nullable|string|max:30',
            'prospect_email'   => 'nullable|email|max:255',
            'prospect_address' => 'nullable|string',
            'category'         => 'required|in:car_film,building_film,ppf',
            'vehicle_brand'    => 'nullable|string|max:100',
            'vehicle_model'    => 'nullable|string|max:100',
            'vehicle_year'     => 'nullable|string|max:10',
            'vehicle_plate'    => 'nullable|string|max:20',
            'discount_amount'  => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string',
            'terms'            => 'nullable|string',
            'assigned_to'      => 'nullable|exists:employees,id',
        ]);

        // Data kendaraan hanya relevan untuk kategori mobil/PPF. Form menyembunyikan
        // field ini via JS saat kategori gedung, tapi input yang disembunyikan tetap
        // ikut ter-submit — kosongkan di server supaya kategori gedung tidak pernah
        // menyimpan data kendaraan (mis. sisa nilai lama sebelum kategori diganti).
        if ($validated['category'] === 'building_film') {
            $validated['vehicle_brand'] = null;
            $validated['vehicle_model'] = null;
            $validated['vehicle_year']  = null;
            $validated['vehicle_plate'] = null;
        }

        return $validated;
    }

    private function validateItems(Request $request): array
    {
        $request->validate([
            'items'                   => 'required|array|min:1',
            'items.*.film_lines'                => 'nullable|array',
            'items.*.film_lines.*.part_id'      => 'required|integer|exists:parts,id',
            'items.*.film_lines.*.category_id'  => 'required|integer|exists:category_products,id',
            'items.*.film_lines.*.product_id'   => 'required|integer|exists:products,id',
            'items.*.description'     => 'required_without:items.*.film_lines|nullable|string|max:255',
            'items.*.specifications'  => 'nullable|string',
            'items.*.unit'            => 'required|string|max:30',
            'items.*.quantity'        => 'required|numeric|min:0.01',
            'items.*.unit_price'      => 'required|numeric|min:0',
            'items.*.discount_percent'=> 'nullable|numeric|min:0|max:100',
        ]);

        $items = $request->input('items', []);

        // Paket kaca film mobil: deskripsi & spesifikasi disusun server dari daftar bagian + tipe + kegelapan.
        $catalog = app(\App\Services\QuotationCatalog::class);

        foreach ($items as $i => $item) {
            $lines = array_values(array_filter((array) ($item['film_lines'] ?? [])));

            if ($request->input('category') !== 'car_film' || ! $lines) {
                $items[$i]['film_lines'] = null;
                continue;
            }

            foreach ($lines as $j => $l) {
                $ok = \App\Models\Product::where('id', (int) $l['product_id'])->where('category_id', (int) $l['category_id'])->exists();
                if (! $ok) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "items.$i.film_lines.$j.product_id" => 'Persentase kegelapan tidak sesuai dengan tipe film yang dipilih.',
                    ]);
                }
            }

            $package = $catalog->composeCarPackage($lines, $item['description'] ?? null);
            $items[$i]['film_lines']     = $package['lines'];
            $items[$i]['description']    = $package['description'];
            $items[$i]['specifications'] = $package['specifications'];
        }

        return $items;
    }

    private function syncItems(Quotation $quotation, array $items): void
    {
        $quotation->items()->delete();

        foreach ($items as $index => $item) {
            $qty  = (float) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $disc  = (float) ($item['discount_percent'] ?? 0);
            $sub   = round($qty * $price * (1 - $disc / 100), 2);

            QuotationItem::create([
                'quotation_id'     => $quotation->id,
                'sort_order'       => $index,
                'description'      => $item['description'],
                'specifications'   => $item['specifications'] ?? null,
                'film_lines'       => $item['film_lines'] ?? null,
                'unit'             => $item['unit'] ?? 'unit',
                'quantity'         => $qty,
                'unit_price'       => $price,
                'discount_percent' => $disc,
                'subtotal'         => $sub,
            ]);
        }
    }

    private function authorizeAccess(Quotation $quotation): void
    {
        $user = auth()->user();
        if ($user->hasRole('superadmin')) return;

        $activeBranchId = $this->branchContext->idOrNull();
        if ((int) $quotation->branch_id !== (int) $activeBranchId) {
            abort(403);
        }
    }
}
