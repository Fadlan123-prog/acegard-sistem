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

    public function create(){
        return view('branches.create', [
            'title' => 'Tambah cabang',
        ]);
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        $branch = Branch::create($validate);

        return redirect()
            ->route('users.create', ['branch_id' => $branch->id])
            ->with('success', 'Cabang berhasil dibuat. Silakan tambah user untuk cabang ini.');
    }

    public function edit(Branch $branch){
        return view('branches.edit', [
            'title' => 'Edit cabang',
            'branch' => $branch,
        ]);
    }

    public function update(Request $request, Branch $branch){
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        $branch->update($validate);

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil diupdate.');
    }

    public function destroy(Branch $branch){
        $branch->delete();

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil dihapus.');
    }
}
