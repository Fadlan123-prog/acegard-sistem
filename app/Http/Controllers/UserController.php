<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['branches', 'roles']);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($branchId = $request->branch_id) {
            $query->whereHas('branches', function ($q) use ($branchId) {
                $q->where('branches.id', $branchId);
            });
        }

        $users    = $query->orderBy('name')->paginate(15);
        $branches = Branch::orderBy('name')->get();

        return view('users.index', [
            'title'    => 'Manajemen User',
            'users'    => $users,
            'branches' => $branches,
        ]);
    }


    public function create(Request $request){
        $branches = Branch::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        $defaultBranchId = $request->query('branch_id');

        return view('users.create', [
            'title' => 'Tambah user',
            'branches' => $branches,
            'roles' => $roles,
            'defaultBranchId' => $defaultBranchId,
        ]);
    }

    public function store(Request $request){
         $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'    => ['required', 'string', 'min:6', 'confirmed'],
            'role'        => ['required', 'string'],
            'branch_ids'  => ['required', 'array'],
            'branch_ids.*'=> ['exists:branches,id'],
        ]);

        DB::transaction(function () use ($validated) {
            // 1. Buat user
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // 2. Assign role Spatie
            $user->assignRole($validated['role']);

            // 3. Assign cabang ke pivot branch_user
            $user->branches()->sync($validated['branch_ids']);
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'User baru berhasil dibuat dan di-assign ke cabang.');
    }

    public function edit(User $user)
    {
        $branches = Branch::orderBy('name')->get();
        $roles    = Role::orderBy('name')->get();

        return view('users.edit', [
            'title'    => 'Edit User',
            'user'     => $user->load('branches', 'roles'),
            'branches' => $branches,
            'roles'    => $roles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password'    => ['nullable', 'string', 'min:6', 'confirmed'],
            'role'        => ['required', 'string'],
            'branch_ids'  => ['required', 'array'],
            'branch_ids.*'=> ['exists:branches,id'],
        ]);

        DB::transaction(function () use ($validated, $user) {
            $user->name  = $validated['name'];
            $user->email = $validated['email'];

            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            $user->syncRoles([$validated['role']]);
            $user->branches()->sync($validated['branch_ids']);
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'selected_ids' => ['required', 'string'],
        ]);

        $ids = collect(explode(',', $request->selected_ids))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Tidak ada user yang dipilih.');
        }

        // Jangan hapus diri sendiri
        $currentUserId = auth()->id();
        $idsToDelete   = $ids->reject(fn ($id) => $id === $currentUserId);

        if ($idsToDelete->isEmpty()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        // Optional: kalau mau cegah penghapusan superadmin lain, bisa filter role di sini

        \App\Models\User::whereIn('id', $idsToDelete)->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User terpilih berhasil dihapus.');
    }

}
