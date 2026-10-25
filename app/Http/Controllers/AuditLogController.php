<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Services\BranchContext;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function index(Request $request)
    {
        $user         = auth()->user();
        $isSuperadmin = $user->hasRole('superadmin');

        $query = AuditLog::query();

        if (!$isSuperadmin) {
            $query->where('branch_id', $this->branch->id());
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('auditable_type', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        $logs = $query->latest('created_at')->paginate(30)->withQueryString();

        $modules  = AuditLog::query()->whereNotNull('module')->distinct()->orderBy('module')->pluck('module');
        $branches = $isSuperadmin ? Branch::orderBy('name')->get() : collect();

        return view('audit-logs.index', [
            'logs'         => $logs,
            'modules'      => $modules,
            'branches'     => $branches,
            'isSuperadmin' => $isSuperadmin,
            // array_merge with defaults: the view reads every one of these keys
            // directly (no ?? fallback), and $request->only() silently omits any
            // key that wasn't actually submitted — e.g. a fresh page load with no
            // query string at all throws "Undefined array key" on the first one.
            'filters'      => array_merge(
                ['branch_id' => null, 'module' => null, 'event' => null, 'from' => null, 'to' => null, 'search' => null],
                $request->only(['branch_id', 'module', 'event', 'from', 'to', 'search'])
            ),
        ]);
    }

    public function show(AuditLog $auditLog)
    {
        $user = auth()->user();

        if (!$user->hasRole('superadmin') && (int) $auditLog->branch_id !== $this->branch->id()) {
            abort(403, 'Tidak boleh melihat log cabang lain.');
        }

        return view('audit-logs.show', [
            'log' => $auditLog,
        ]);
    }
}
