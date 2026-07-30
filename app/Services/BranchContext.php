<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\Cache;

/**
 * Single canonical source for the active branch during a request.
 * Registered as a singleton in AppServiceProvider.
 *
 * Replaces 15+ raw session('active_branch_id') reads scattered across controllers.
 * The first call per request hits Redis (5-min TTL); subsequent calls return
 * the in-process resolved instance at zero I/O cost.
 *
 * Usage in controllers:
 *   public function __construct(private BranchContext $branch) {}
 *   $branchId = $this->branch->id();
 *   $branch   = $this->branch->current();
 */
final class BranchContext
{
    private ?Branch $resolved = null;

    /**
     * Returns the active Branch model for the current request.
     * Aborts 400 if no branch is selected (programmer error — middleware should guard first).
     */
    public function current(): Branch
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $id = session('active_branch_id');

        if (! $id) {
            abort(400, 'No active branch selected. Ensure EnsureBranchAccess middleware is applied.');
        }

        $this->resolved = Cache::remember(
            "branch.{$id}",
            now()->addMinutes(5),
            fn () => Branch::findOrFail($id)
        );

        return $this->resolved;
    }

    public function id(): int
    {
        return $this->current()->id;
    }

    public function name(): string
    {
        return $this->current()->name;
    }

    /**
     * Force the context to a specific branch (used in tests and CLI commands).
     * Does NOT write to session — call session(['active_branch_id' => $id]) separately if needed.
     */
    public function forceSet(Branch $branch): void
    {
        $this->resolved = $branch;
    }

    /**
     * Evict the in-process and Redis-cached branch.
     * Call after branch data changes (name, city, etc.) to prevent stale reads.
     */
    public function flush(): void
    {
        $id = session('active_branch_id');

        if ($id) {
            Cache::forget("branch.{$id}");
        }

        $this->resolved = null;
    }

    /**
     * Switch the active branch for the session and reset the in-process cache.
     * Call from the branch-switch endpoint in DashboardController.
     */
    public function switchTo(int $branchId): void
    {
        $this->resolved = null;
        session(['active_branch_id' => $branchId]);
    }

    /**
     * Returns the active branch ID, or null when no branch is selected.
     * Use this in superadmin paths where a global (no-branch) view is valid.
     */
    public function idOrNull(): ?int
    {
        $id = session('active_branch_id');
        return $id ? (int) $id : null;
    }

    /**
     * Returns true when a branch is set and resolvable (used in optional-branch contexts).
     */
    public function isSet(): bool
    {
        return (bool) session('active_branch_id');
    }
}
