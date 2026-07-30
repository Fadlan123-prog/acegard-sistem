<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Services\BranchContext;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request, BranchContext $branchContext)
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

        $query = Lead::accessibleByUser()
            ->tap($marketingScope)
            ->with(['assignee', 'branch'])
            ->latest();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('branch_id') && auth()->user()->hasRole('superadmin')) {
            $query->where('branch_id', $request->branch_id);
        }

        $leads = $query->paginate(20)->withQueryString();

        $statusCounts = Lead::accessibleByUser()
            ->tap($marketingScope)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $branches = auth()->user()->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : collect();

        $employees = Employee::where('branch_id', $branchContext->idOrNull())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('leads.index', compact('leads', 'statusCounts', 'branches', 'employees'));
    }

    public function create(BranchContext $branchContext)
    {
        $employees = Employee::where('branch_id', $branchContext->idOrNull())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $branches = auth()->user()->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : collect();

        return view('leads.create', compact('employees', 'branches'));
    }

    public function store(Request $request, BranchContext $branchContext)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string',
            'city'            => 'nullable|string|max:100',
            'source'          => 'required|in:walk_in,referral,social_media,website,whatsapp,cold_call,other',
            'category'        => 'required|in:car_film,building_film,ppf',
            'vehicle_brand'   => 'nullable|string|max:100',
            'vehicle_model'   => 'nullable|string|max:100',
            'vehicle_year'    => 'nullable|string|max:10',
            'notes'           => 'nullable|string',
            'assigned_to'     => 'nullable|exists:employees,id',
            'estimated_value' => 'nullable|numeric|min:0',
            'follow_up_date'  => 'nullable|date',
        ]);

        $branchId = auth()->user()->hasRole('superadmin') && $request->filled('branch_id')
            ? $request->branch_id
            : $branchContext->idOrNull();

        $lead = Lead::create([
            ...$validated,
            'branch_id'  => $branchId,
            'status'     => 'new',
            'created_by' => auth()->id(),
        ]);

        LeadActivity::create([
            'lead_id'    => $lead->id,
            'type'       => 'note',
            'notes'      => 'Lead dibuat.',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead berhasil ditambahkan.');
    }

    public function show(Lead $lead)
    {
        $this->authorizeLeadAccess($lead);

        $lead->load(['branch', 'assignee', 'creator', 'activities.creator']);

        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead, BranchContext $branchContext)
    {
        $this->authorizeLeadAccess($lead);

        $employees = Employee::where('branch_id', $lead->branch_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('leads.edit', compact('lead', 'employees'));
    }

    public function update(Request $request, Lead $lead)
    {
        $this->authorizeLeadAccess($lead);

        $oldStatus = $lead->status;

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string',
            'city'            => 'nullable|string|max:100',
            'source'          => 'required|in:walk_in,referral,social_media,website,whatsapp,cold_call,other',
            'category'        => 'required|in:car_film,building_film,ppf',
            'vehicle_brand'   => 'nullable|string|max:100',
            'vehicle_model'   => 'nullable|string|max:100',
            'vehicle_year'    => 'nullable|string|max:10',
            'notes'           => 'nullable|string',
            'status'          => 'required|in:new,contacted,follow_up,quoted,won,lost',
            'lost_reason'     => 'nullable|required_if:status,lost|string',
            'assigned_to'     => 'nullable|exists:employees,id',
            'estimated_value' => 'nullable|numeric|min:0',
            'follow_up_date'  => 'nullable|date',
        ]);

        $lead->update($validated);

        if ($oldStatus !== $validated['status']) {
            LeadActivity::create([
                'lead_id'    => $lead->id,
                'type'       => 'status_change',
                'notes'      => 'Status diubah dari "' . Lead::STATUSES[$oldStatus] . '" ke "' . Lead::STATUSES[$validated['status']] . '".',
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead berhasil diperbarui.');
    }

    public function destroy(Lead $lead)
    {
        $this->authorizeLeadAccess($lead);

        $lead->delete();

        return redirect()->route('leads.index')
            ->with('success', 'Lead berhasil dihapus.');
    }

    public function addActivity(Request $request, Lead $lead)
    {
        $this->authorizeLeadAccess($lead);

        $validated = $request->validate([
            'type'  => 'required|in:call,whatsapp,visit,email,note',
            'notes' => 'required|string|max:1000',
        ]);

        LeadActivity::create([
            ...$validated,
            'lead_id'    => $lead->id,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Aktivitas berhasil dicatat.');
    }

    private function authorizeLeadAccess(Lead $lead): void
    {
        $user = auth()->user();

        if ($user->hasRole('superadmin')) {
            return;
        }

        $activeBranchId = app(BranchContext::class)->idOrNull();

        if ((int) $lead->branch_id !== (int) $activeBranchId) {
            abort(403);
        }

        // Marketing hanya bisa lihat lead yang ditugaskan ke mereka
        if ($user->hasRole('marketing') && $user->employee) {
            abort_unless(
                (int) $lead->assigned_to === (int) $user->employee->id
                || (int) $lead->created_by === (int) $user->id,
                403
            );
        }
    }
}
