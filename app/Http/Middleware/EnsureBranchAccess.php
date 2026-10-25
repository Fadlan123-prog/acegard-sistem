<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Route yang tidak perlu validasi branch
        |--------------------------------------------------------------------------
        */
        if ($request->routeIs('notifications.open')) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Superadmin boleh akses semua cabang
        |--------------------------------------------------------------------------
        */
        if ($user->hasRole('superadmin')) {
            $this->ensureSuperadminHasActiveBranch();

            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Staff dengan branch assignment (admin, manager, marketing, finance, dst)
        |--------------------------------------------------------------------------
        */
        $staffRoles = ['admin', 'owner', 'manager_operasional', 'manager_marketing', 'marketing', 'finance', 'purchasing', 'customer_service', 'it_manager'];

        if ($user->hasAnyRole($staffRoles)) {
            $this->ensureAdminHasValidBranch($user);

            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Teknisi & kenek menggunakan route group mereka sendiri — tidak lewat sini
        | Role lain yang tidak dikenal tidak diizinkan
        |--------------------------------------------------------------------------
        */
        session()->forget('active_branch_id');

        abort(403, 'Akun kamu tidak memiliki akses ke sistem ini.');
    }

    private function ensureSuperadminHasActiveBranch(): void
    {
        $sessionBranchId = session('active_branch_id');

        if ($this->branchExists($sessionBranchId)) {
            return;
        }

        $defaultBranch = $this->getDefaultBranchForSuperadmin();

        if ($defaultBranch) {
            session(['active_branch_id' => $defaultBranch->id]);
        }
    }

    private function ensureAdminHasValidBranch($user): void
    {
        $branches = $user->branches()
            ->orderBy('branches.id')
            ->get();

        if ($branches->isEmpty()) {
            session()->forget('active_branch_id');

            abort(403, 'Kamu tidak memiliki akses ke cabang manapun.');
        }

        $allowedBranchIds = $branches
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        $sessionBranchId = session('active_branch_id');

        if (
            $sessionBranchId &&
            $allowedBranchIds->contains((int) $sessionBranchId)
        ) {
            return;
        }

        session(['active_branch_id' => $branches->first()->id]);
    }

    private function branchExists($branchId): bool
    {
        if (!$branchId) {
            return false;
        }

        return Branch::query()
            ->where('id', $branchId)
            ->exists();
    }

    private function getDefaultBranchForSuperadmin(): ?Branch
    {
        $defaultBranch = Branch::query()
            ->where('name', 'like', '%pusat%')
            ->orWhere('city', 'like', '%pusat%')
            ->orderBy('id')
            ->first();

        if ($defaultBranch) {
            return $defaultBranch;
        }

        return Branch::query()
            ->orderBy('id')
            ->first();
    }
}
