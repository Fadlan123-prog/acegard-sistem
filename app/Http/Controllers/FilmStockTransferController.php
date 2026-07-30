<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\FilmRollEntry;
use App\Models\FilmStockTransfer;
use App\Services\BranchContext;
use App\Services\FilmStockTransferService;
use Illuminate\Http\Request;

class FilmStockTransferController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function index(Request $request)
    {
        $query = FilmStockTransfer::with([
            'fromBranch',
            'toBranch',
            'creator',
            'receiver',
            'items',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('transfer_number', 'like', "%{$search}%")
                    ->orWhereHas('fromBranch', function ($b) use ($search) {
                        $b->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('toBranch', function ($b) use ($search) {
                        $b->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('transfer_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('transfer_date', '<=', $request->to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transfers = $query
            ->latest('transfer_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('film-stock-transfers.index', compact('transfers'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();

        $branches = $user->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : $user->branches()->orderBy('name')->get();

        $fromBranchId = $request->input('from_branch_id') ?? $this->branch->idOrNull();

        $availableRolls = collect();

        if ($fromBranchId) {
            $availableRolls = FilmRollEntry::with(['stockPool', 'currentBranch'])
                ->where('current_branch_id', $fromBranchId)
                ->whereIn('status', ['in_warehouse', 'in_branch'])
                ->latest('received_date')
                ->get();
        }

        return view('film-stock-transfers.create', compact(
            'branches',
            'availableRolls',
            'fromBranchId'
        ));
    }

    public function store(Request $request, FilmStockTransferService $transferService)
    {
        $data = $request->validate([
            'from_branch_id' => ['required', 'exists:branches,id'],
            'to_branch_id' => ['required', 'exists:branches,id', 'different:from_branch_id'],
            'transfer_date' => ['required', 'date'],
            'roll_entry_ids' => ['required', 'array', 'min:1'],
            'roll_entry_ids.*' => ['required', 'exists:film_roll_entries,id'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $transfer = $transferService->transfer($data);

            return redirect()
                ->route('film-stock-transfers.show', $transfer->id)
                ->with('success', 'Transfer roll berhasil dibuat.');

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal transfer roll: ' . $e->getMessage());
        }
    }

    public function show(FilmStockTransfer $transfer)
    {
        $transfer->load([
            'fromBranch',
            'toBranch',
            'creator',
            'receiver',
            'items.rollEntry',
            'items.stockPool',
        ]);

        return view('film-stock-transfers.show', compact('transfer'));
    }

    public function receive(FilmStockTransfer $transfer)
    {
        if ($transfer->status !== 'sent') {
            return back()->with('error', 'Transfer ini tidak bisa diterima karena status bukan sent.');
        }

        $transfer->update([
            'status' => 'received',
            'received_by' => auth()->id(),
        ]);

        return back()->with('success', 'Transfer berhasil dikonfirmasi diterima.');
    }
}
