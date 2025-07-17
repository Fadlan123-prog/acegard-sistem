<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function switch(Request $request)
    {
        $user = auth()->user();
        $branchId = $request->branch_id;

        if (!$user->hasRole('superadmin') && !$user->branches->pluck('id')->contains($branchId)) {
            abort(403, 'Tidak boleh pilih cabang lain.');
        }

        session(['active_branch_id' => $branchId]);
        return back()->with('success', 'Cabang aktif diubah.');
    }
}
