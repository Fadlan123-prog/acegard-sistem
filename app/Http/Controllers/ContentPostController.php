<?php

namespace App\Http\Controllers;

use App\Models\ContentPost;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContentPostController extends Controller
{
    /**
     * Content/live roles (designer, videographer, strategi_pengembangan) are not
     * branch-bound via session like admin/marketing — their branch comes from
     * their own employee record. Superadmin/owner/manager_marketing see all
     * branches, optionally filtered by ?branch_id=.
     */
    private function branchId(Request $request): ?int
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['superadmin', 'owner', 'manager_marketing'])) {
            return $request->filled('branch_id') ? (int) $request->query('branch_id') : null;
        }

        return $user->employee->branch_id ?? null;
    }

    private function ownEmployeeId(Request $request): ?int
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['superadmin', 'owner', 'manager_marketing'])) {
            return null;
        }

        return $user->employee_id;
    }

    public function dashboard(Request $request)
    {
        $branchId   = $this->branchId($request);
        $employeeId = $this->ownEmployeeId($request);
        $month      = (int) $request->query('month', now()->month);
        $year       = (int) $request->query('year', now()->year);

        $base = ContentPost::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($employeeId, fn ($q) => $q->where('employee_id', $employeeId))
            ->whereMonth('published_at', $month)
            ->whereYear('published_at', $year);

        $totalPosts  = (clone $base)->count();
        $totalViews  = (clone $base)->sum('views');
        $fypCount    = (clone $base)->where('is_fyp', true)->count();
        $organicLeads = (clone $base)->sum('leads_generated');

        $topPosts = (clone $base)->with('employee')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        $byCreator = ContentPost::query()
            ->join('employees', 'content_posts.employee_id', '=', 'employees.id')
            ->when($branchId, fn ($q) => $q->where('content_posts.branch_id', $branchId))
            ->whereMonth('content_posts.published_at', $month)
            ->whereYear('content_posts.published_at', $year)
            ->select(
                'employees.id', 'employees.name',
                DB::raw('COUNT(*) as total_posts'),
                DB::raw('SUM(content_posts.views) as total_views'),
                DB::raw('SUM(CASE WHEN content_posts.is_fyp THEN 1 ELSE 0 END) as fyp_count'),
            )
            ->groupBy('employees.id', 'employees.name')
            ->orderByDesc('total_views')
            ->get();

        $byPlatform = (clone $base)
            ->select('platform', DB::raw('COUNT(*) as total'))
            ->groupBy('platform')
            ->pluck('total', 'platform')
            ->all();

        return view('content.dashboard', [
            'byPlatform'   => $byPlatform,
            'totalPosts'   => $totalPosts,
            'totalViews'   => $totalViews,
            'fypCount'     => $fypCount,
            'organicLeads' => $organicLeads,
            'topPosts'     => $topPosts,
            'byCreator'    => $byCreator,
            'month'        => $month,
            'year'         => $year,
        ]);
    }

    public function index(Request $request)
    {
        $branchId   = $this->branchId($request);
        $employeeId = $this->ownEmployeeId($request);

        $posts = ContentPost::with(['employee', 'branch'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($employeeId, fn ($q) => $q->where('employee_id', $employeeId))
            ->orderByDesc('published_at')
            ->paginate(20)
            ->withQueryString();

        return view('content.posts.index', compact('posts'));
    }

    public function create()
    {
        $user = auth()->user();
        $employees = $user->hasAnyRole(['superadmin', 'owner', 'manager_marketing'])
            ? Employee::where('is_active', true)->orderBy('name')->get()
            : Employee::where('id', $user->employee_id)->get();

        return view('content.posts.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'     => 'required|exists:employees,id',
            'platform'        => 'required|in:' . implode(',', array_keys(ContentPost::PLATFORMS)),
            'title'           => 'required|string|max:255',
            'published_at'    => 'required|date',
            'views'           => 'required|integer|min:0',
            'engagement_rate' => 'nullable|numeric|min:0|max:100',
            'leads_generated' => 'required|integer|min:0',
            'notes'           => 'nullable|string|max:255',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $user     = auth()->user();

        if (! $user->hasAnyRole(['superadmin', 'owner', 'manager_marketing']) && (int) $employee->id !== (int) $user->employee_id) {
            abort(403, 'Hanya bisa mencatat konten atas nama diri sendiri.');
        }

        ContentPost::create([
            ...$validated,
            'branch_id'  => $employee->branch_id,
            'is_fyp'     => $validated['views'] >= ContentPost::FYP_VIEWS_THRESHOLD,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('content.posts.index')->with('success', 'Konten berhasil dicatat.');
    }

    public function destroy(ContentPost $post)
    {
        $user = auth()->user();

        if (! $user->hasAnyRole(['superadmin', 'owner', 'manager_marketing']) && (int) $post->employee_id !== (int) $user->employee_id) {
            abort(403);
        }

        $post->delete();

        return back()->with('success', 'Konten dihapus.');
    }
}
