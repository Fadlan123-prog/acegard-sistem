<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Superadmin bebas akses
        if ($user->hasRole('superadmin')) {
            return $next($request);
        }

        // Cek apakah user punya cabang
        $branches = $user->branches;
        if ($branches->isEmpty()) {
            abort(403, 'Kamu tidak memiliki akses ke cabang manapun.');
        }

        // Ambil session atau fallback ke cabang pertama
        $branchId = session('active_branch_id') ?? $branches->first()->id;

        // Set session jika belum ada
        if (!session()->has('active_branch_id')) {
            session(['active_branch_id' => $branchId]);
        }

        // Cek apakah branchId valid
        if (!$branches->pluck('id')->contains($branchId)) {
            abort(403, 'Kamu tidak memiliki akses ke cabang ini.');
        }

        return $next($request);
    }
}
