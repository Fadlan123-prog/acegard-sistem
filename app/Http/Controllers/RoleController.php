<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    // Deleting this would lock everyone out of role/permission management — never allowed.
    private const PROTECTED_ROLE = 'superadmin';

    public function index()
    {
        $roles = Role::withCount('permissions')
            ->orderBy('name')
            ->get()
            ->map(function (Role $role) {
                $role->users_count = DB::table('model_has_roles')->where('role_id', $role->id)->count();
                return $role;
            });

        return view('roles.index', [
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();

        return view('roles.create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255', 'regex:/^[a-z_]+$/', 'unique:roles,name'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ], [
            'name.regex' => 'Nama role hanya boleh huruf kecil dan underscore, contoh: manager_gudang.',
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', "Role \"{$role->name}\" berhasil dibuat.");
    }

    public function edit(Role $role)
    {
        $permissions   = Permission::orderBy('name')->get();
        $rolePermIds   = $role->permissions()->pluck('permissions.id')->all();

        return view('roles.edit', [
            'role'         => $role,
            'permissions'  => $permissions,
            'rolePermIds'  => $rolePermIds,
            'isProtected'  => $role->name === self::PROTECTED_ROLE,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255', 'regex:/^[a-z_]+$/', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ], [
            'name.regex' => 'Nama role hanya boleh huruf kecil dan underscore, contoh: manager_gudang.',
        ]);

        // Renaming the protected role would break every `role:superadmin|...` route
        // gate in routes/web.php — the permission set can still change, just not the name.
        if ($role->name === self::PROTECTED_ROLE) {
            $data['name'] = self::PROTECTED_ROLE;
        }

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', "Role \"{$role->name}\" berhasil diperbarui.");
    }

    public function destroy(Role $role)
    {
        if ($role->name === self::PROTECTED_ROLE) {
            return back()->with('error', 'Role superadmin tidak dapat dihapus.');
        }

        $userCount = DB::table('model_has_roles')->where('role_id', $role->id)->count();

        if ($userCount > 0) {
            return back()->with('error', "Role \"{$role->name}\" masih digunakan oleh {$userCount} user dan tidak dapat dihapus. Pindahkan user tersebut ke role lain terlebih dahulu.");
        }

        $name = $role->name;
        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', "Role \"{$name}\" berhasil dihapus.");
    }
}
