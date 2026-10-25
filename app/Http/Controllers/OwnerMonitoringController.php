<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\CreativeTask;
use App\Models\DevelopmentAnnualPlan;
use App\Models\DevelopmentReport;
use App\Models\EmployeeClaim;
use App\Models\ExportOrder;
use App\Models\ImportOrder;

class OwnerMonitoringController extends Controller
{
    public function index()
    {
        // Approval queues (generic system, Phase 1) — how much is waiting, by flow
        $pendingByFlow = ApprovalRequest::with('flow')
            ->where('status', 'pending')
            ->get()
            ->groupBy(fn ($r) => $r->flow->name)
            ->map->count();

        // Teknisi/Kenek claims (Phase 3)
        $claimsByStatus = EmployeeClaim::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Team Creative workload (Phase 4)
        $creativeByStatus = CreativeTask::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $overdueCreativeTasks = CreativeTask::whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['done'])
            ->count();

        // Export & Import (Phase 6)
        $importByStatus = ImportOrder::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $exportByStatus = ExportOrder::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        // Development (Phase 7)
        $latestReport = DevelopmentReport::with('author')->latest('period_end')->first();
        $currentYearPlan = DevelopmentAnnualPlan::with('initiatives')->where('year', now()->year)->first();

        return view('owner-monitoring.index', compact(
            'pendingByFlow', 'claimsByStatus', 'creativeByStatus', 'overdueCreativeTasks',
            'importByStatus', 'exportByStatus', 'latestReport', 'currentYearPlan'
        ));
    }
}
