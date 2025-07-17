<?php

namespace App\Traits;

trait HasBranchScope
{
    /**
     * Scope global berdasarkan cabang yang diakses user
     */
    public function scopeAccessibleByUser($query)
    {
        $user = auth()->user();

        // Jika superadmin, tampilkan semua
        if ($user && $user->hasRole('superadmin')) {
            return $query;
        }

        // Kalau user biasa, tampilkan sesuai cabang aktif
        $activeBranchId = session('active_branch_id');

        if ($activeBranchId) {
            return $query->where('branch_id', $activeBranchId);
        }

        // Jika tidak ada cabang aktif, kosongkan hasil
        return $query->whereRaw('1 = 0');
    }
}
