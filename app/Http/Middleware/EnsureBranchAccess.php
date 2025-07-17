<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        $branchId = session('active_branch_id');

        // Superadmin bebas akses
        if ($user->hasRole('superadmin')) {
            return $next($request);
        }

        // User biasa: hanya bisa akses cabang yang dia punya
        if (!$user->branches->pluck('id')->contains($branchId)) {
            abort(403, 'Kamu tidak memiliki akses ke cabang ini.');
        }

        return $next($request);
    }
}
