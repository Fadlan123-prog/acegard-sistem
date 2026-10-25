<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\WarrantyClaim;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerBuilding;
use App\Models\CustomerPpfInstallation;

class AdminWarrantyClaimController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $branches = $user->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : $user->branches()->orderBy('name')->get();

        $query = WarrantyClaim::query()
            ->with(['items','invoice'])
            ->with(['warrantable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Customer::class => [],
                    CustomerBuilding::class => [],
                    CustomerPpfInstallation::class => ['customer'],
                ]);
            }]);

        if (!$user->hasRole('superadmin')) {
            $allowed = $user->branches()->pluck('branches.id');
            $query->whereIn('branch_id', $allowed);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('warranty_type', $request->type);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('claim_number','like',"%$s%")
                  ->orWhere('card_number','like',"%$s%")
                  ->orWhere('code_entered','like',"%$s%");
            });
        }

        $claims = $query->latest()->paginate(10)->withQueryString();

        return view('admin.claims.index', compact('claims','branches'));
    }

    public function show(WarrantyClaim $claim)
    {
        $claim->load(['items','invoice.items']);

        // load detail tergantung tipe
        $claim->loadMorph('warrantable', [
            Customer::class => [
                'branch','customerProducts.part','customerProducts.product','customerProducts.categoryProduct',
            ],
            CustomerBuilding::class => ['branch'],
            CustomerPpfInstallation::class => [
                'branch','ppfProduct','customer','technicians:id,name'
            ],
        ]);

        // security: non superadmin hanya boleh claim cabangnya
        $user = auth()->user();
        if (!$user->hasRole('superadmin')) {
            $allowed = $user->branches()->pluck('branches.id')->toArray();
            abort_unless(in_array($claim->branch_id, $allowed), 403);
        }

        return view('admin.claims.show', compact('claim'));
    }

    public function inspect(Request $request, WarrantyClaim $claim)
    {
        $user = auth()->user();
        if (!$user->hasRole('superadmin')) {
            $allowed = $user->branches()->pluck('branches.id')->toArray();
            abort_unless(in_array($claim->branch_id, $allowed), 403);
        }

        $data = $request->validate([
            'cause' => ['required','in:workmanship,product,user,unknown'],
            'inspection_notes' => ['nullable','string','max:5000'],

            'items' => ['nullable','array'],
            'items.*.action' => ['nullable','in:repair,replace'],
            'items.*.charge_amount' => ['nullable','integer','min:0'],
            'items.*.notes' => ['nullable','string','max:2000'],
        ]);

        $cause = $data['cause'];
        $isChargeable = ($cause === 'user');

        // update items
        if (!empty($data['items'])) {
            foreach ($data['items'] as $itemId => $row) {
                $item = $claim->items()->where('id', $itemId)->first();
                if (!$item) continue;

                $item->action = $row['action'] ?? $item->action;
                $item->charge_amount = $isChargeable ? (int)($row['charge_amount'] ?? $item->charge_amount) : 0;
                $item->notes = $row['notes'] ?? $item->notes;
                $item->save();
            }
        } else {
            // kalau user cause tapi belum input, default tetap
            if (!$isChargeable) {
                $claim->items()->update(['charge_amount' => 0]);
            }
        }

        $quoteTotal = (int)$claim->items()->sum('charge_amount');

        $claim->cause = $cause;
        $claim->inspection_notes = $data['inspection_notes'] ?? $claim->inspection_notes;
        $claim->is_chargeable = $isChargeable;
        $claim->quote_total = $isChargeable ? $quoteTotal : 0;

        // minimal: setelah inspeksi set status inspected (kecuali sudah rejected/closed)
        if (in_array($claim->status, ['submitted','inspected'])) {
            $claim->status = 'inspected';
        }

        $claim->save();

        return back()->with('success', 'Hasil inspeksi tersimpan.');
    }

    public function updateStatus(Request $request, WarrantyClaim $claim)
    {
        $user = auth()->user();
        if (!$user->hasRole('superadmin')) {
            $allowed = $user->branches()->pluck('branches.id')->toArray();
            abort_unless(in_array($claim->branch_id, $allowed), 403);
        }

        $data = $request->validate([
            'status' => ['required','in:approved,rejected,in_progress,completed,closed'],
            'resolution_notes' => ['nullable','string','max:5000'],
        ]);

        // validasi sederhana: chargeable approved tapi belum ada invoice => arahkan bikin invoice
        if ($data['status'] === 'approved' && $claim->is_chargeable && !$claim->invoice) {
            return redirect()
                ->route('admin.claim-invoices.create', $claim->id)
                ->with('error', 'Claim ini chargeable. Buat invoice terlebih dahulu.');
        }

        $claim->status = $data['status'];
        $claim->resolution_notes = $data['resolution_notes'] ?? $claim->resolution_notes;

        if ($data['status'] === 'completed') $claim->completed_at = now();
        if ($data['status'] === 'closed')    $claim->closed_at = now();

        $claim->save();

        return back()->with('success', 'Status claim diperbarui.');
    }
}
