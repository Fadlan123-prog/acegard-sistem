<?php

namespace App\Http\Controllers;

use App\Models\AdSpendEntry;
use App\Models\Lead;
use App\Services\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdvertisingController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    private function branchId(): ?int
    {
        $user = auth()->user();

        if ($user->hasRole('superadmin') || $user->hasRole('owner')) {
            return $this->branch->idOrNull();
        }

        return $this->branch->id();
    }

    public function dashboard(Request $request)
    {
        $branchId = $this->branchId();
        $month    = (int) $request->query('month', now()->month);
        $year     = (int) $request->query('year', now()->year);

        $spendByChannel = AdSpendEntry::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereMonth('spend_date', $month)
            ->whereYear('spend_date', $year)
            ->select('channel', DB::raw('SUM(amount) as total_spend'))
            ->groupBy('channel')
            ->pluck('total_spend', 'channel');

        $leadsByChannel = Lead::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereNotNull('ad_channel')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->select(
                'ad_channel',
                DB::raw('COUNT(*) as total_leads'),
                DB::raw('SUM(CASE WHEN status = "won" THEN 1 ELSE 0 END) as won_leads'),
                DB::raw('SUM(CASE WHEN status = "won" THEN estimated_value ELSE 0 END) as won_value'),
            )
            ->groupBy('ad_channel')
            ->get()
            ->keyBy('ad_channel');

        $channels = [];
        foreach (Lead::AD_CHANNELS as $key => $label) {
            $spend = (float) ($spendByChannel[$key] ?? 0);
            $leads = $leadsByChannel[$key] ?? null;
            $totalLeads = $leads->total_leads ?? 0;
            $wonLeads   = $leads->won_leads ?? 0;
            $wonValue   = (float) ($leads->won_value ?? 0);

            $channels[] = [
                'key'          => $key,
                'label'        => $label,
                'spend'        => $spend,
                'leads'        => $totalLeads,
                'cpl'          => $totalLeads > 0 ? $spend / $totalLeads : null,
                'won_leads'    => $wonLeads,
                'closing_rate' => $totalLeads > 0 ? round($wonLeads / $totalLeads * 100, 1) : 0,
                'attributed_revenue' => $wonValue,
            ];
        }

        $totalSpend = array_sum(array_column($channels, 'spend'));
        $totalLeads = array_sum(array_column($channels, 'leads'));
        $avgCpl     = $totalLeads > 0 ? $totalSpend / $totalLeads : null;
        $attributedRevenue = array_sum(array_column($channels, 'attributed_revenue'));

        // Funnel across the whole lead base for the period (not only paid channels),
        // mapped from Lead::STATUSES — mirrors the mockup's "Leads masuk → Qualified →
        // Follow up → Closing" stages.
        $funnel = Lead::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('advertising.dashboard', [
            'channels'          => $channels,
            'totalSpend'        => $totalSpend,
            'totalLeads'        => $totalLeads,
            'avgCpl'            => $avgCpl,
            'attributedRevenue' => $attributedRevenue,
            'funnel'            => $funnel,
            'month'             => $month,
            'year'              => $year,
        ]);
    }

    public function index(Request $request)
    {
        $branchId = $this->branchId();
        $month    = (int) $request->query('month', now()->month);
        $year     = (int) $request->query('year', now()->year);

        $entries = AdSpendEntry::with(['branch', 'creator'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereMonth('spend_date', $month)
            ->whereYear('spend_date', $year)
            ->orderByDesc('spend_date')
            ->paginate(20)
            ->withQueryString();

        return view('advertising.spend.index', compact('entries', 'month', 'year'));
    }

    public function create()
    {
        return view('advertising.spend.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'channel'    => 'required|in:' . implode(',', array_keys(AdSpendEntry::CHANNELS)),
            'spend_date' => 'required|date',
            'amount'     => 'required|numeric|min:0',
            'notes'      => 'nullable|string|max:255',
        ]);

        $branchId = $this->branchId();

        if (! $branchId) {
            return back()->withInput()->withErrors(['branch_id' => 'Pilih cabang aktif terlebih dahulu.']);
        }

        AdSpendEntry::create([
            ...$validated,
            'branch_id'  => $branchId,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('advertising.spend.index')->with('success', 'Spend iklan berhasil dicatat.');
    }

    public function destroy(AdSpendEntry $spend)
    {
        $user = auth()->user();

        if (! $user->hasRole('superadmin') && (int) $spend->branch_id !== $this->branch->idOrNull()) {
            abort(403);
        }

        $spend->delete();

        return back()->with('success', 'Entri spend dihapus.');
    }
}
