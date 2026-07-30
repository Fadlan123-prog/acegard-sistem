<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private array $allowedRoles = [
        'superadmin', 'owner', 'admin',
        'manager_operasional', 'manager_marketing',
        'marketing', 'finance', 'purchasing',
        'customer_service', 'teknisi', 'kenek',
    ];

    // Roles whose users bypass the branch middleware (no branch_user row needed)
    private array $staffRoles = ['teknisi', 'kenek'];

    // Roles that must be linked to an employee record (for data scoping)
    private array $employeeRoles = ['teknisi', 'kenek', 'marketing'];

    // Roles that do NOT need branch assignment
    private array $noBranchRoles = ['superadmin', 'teknisi', 'kenek'];

    public function index(Request $request)
    {
        $search = trim((string) $request->get('search'));
        $branchId = $request->get('branch_id');

        $users = User::query()
            ->with(['branches:id,name,city', 'roles:id,name'])
            ->withCount('branches')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('branches', function ($q) use ($branchId) {
                    $q->where('branches.id', $branchId);
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $branches = Branch::query()
            ->select('id', 'name', 'city')
            ->orderBy('name')
            ->get();

        return view('users.index', [
            'title'    => 'Manajemen User',
            'users'    => $users,
            'branches' => $branches,
            'filters'  => [
                'search'    => $search,
                'branch_id' => $branchId,
            ],
        ]);
    }

    public function create(Request $request)
    {
        $branches = Branch::query()
            ->select('id', 'name', 'city')
            ->orderBy('name')
            ->get();

        $roles = Role::query()
            ->whereIn('name', $this->allowedRoles)
            ->orderBy('name')
            ->get();

        $employees = Employee::query()
            ->select('id', 'name', 'job_position', 'branch_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $defaultBranchId = null;

        if ($request->filled('branch_id')) {
            $defaultBranchId = Branch::query()
                ->where('id', $request->branch_id)
                ->value('id');
        }

        return view('users.create', [
            'title'           => 'Tambah User',
            'branches'        => $branches,
            'roles'           => $roles,
            'employees'       => $employees,
            'staffRoles'      => $this->staffRoles,
            'employeeRoles'   => $this->employeeRoles,
            'noBranchRoles'   => $this->noBranchRoles,
            'defaultBranchId' => $defaultBranchId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateUser($request);

        DB::transaction(function () use ($validated) {
            $needsEmployee = in_array($validated['role'], $this->employeeRoles);
            $needsBranch   = !in_array($validated['role'], $this->noBranchRoles);

            $user = User::create([
                'name'        => $validated['name'],
                'email'       => $validated['email'],
                'password'    => Hash::make($validated['password']),
                'employee_id' => $needsEmployee ? ($validated['employee_id'] ?? null) : null,
            ]);

            $user->syncRoles([$validated['role']]);

            if ($needsBranch) {
                $user->branches()->sync($validated['branch_ids'] ?? []);
            }
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'User baru berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $user->load(['branches:id,name,city', 'roles:id,name', 'employee:id,name,job_position']);

        $branches = Branch::query()
            ->select('id', 'name', 'city')
            ->orderBy('name')
            ->get();

        $roles = Role::query()
            ->whereIn('name', $this->allowedRoles)
            ->orderBy('name')
            ->get();

        $employees = Employee::query()
            ->select('id', 'name', 'job_position', 'branch_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('users.edit', [
            'title'         => 'Edit User',
            'user'          => $user,
            'branches'      => $branches,
            'roles'         => $roles,
            'employees'     => $employees,
            'staffRoles'    => $this->staffRoles,
            'employeeRoles' => $this->employeeRoles,
            'noBranchRoles' => $this->noBranchRoles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $this->validateUser($request, $user);

        DB::transaction(function () use ($validated, $user) {
            $needsEmployee = in_array($validated['role'], $this->employeeRoles);
            $needsBranch   = !in_array($validated['role'], $this->noBranchRoles);

            $updateData = [
                'name'        => $validated['name'],
                'email'       => $validated['email'],
                'employee_id' => $needsEmployee ? ($validated['employee_id'] ?? null) : null,
            ];

            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            $user->syncRoles([$validated['role']]);

            if ($needsBranch) {
                $user->branches()->sync($validated['branch_ids'] ?? []);
            } else {
                $user->branches()->detach();
            }
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ((int) auth()->id() === (int) $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        DB::transaction(function () use ($user) {
            $user->branches()->detach();
            $user->syncRoles([]);
            $user->delete();
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate(
            [
                'selected_ids' => ['required', 'string'],
            ],
            [
                'selected_ids.required' => 'Tidak ada user yang dipilih.',
            ]
        );

        $ids = collect(explode(',', $validated['selected_ids']))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Tidak ada user yang dipilih.');
        }

        $currentUserId = (int) auth()->id();

        $idsToDelete = $ids
            ->reject(fn ($id) => (int) $id === $currentUserId)
            ->values();

        if ($idsToDelete->isEmpty()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        DB::transaction(function () use ($idsToDelete) {
            $users = User::query()
                ->whereIn('id', $idsToDelete)
                ->get();

            foreach ($users as $user) {
                $user->branches()->detach();
                $user->syncRoles([]);
                $user->delete();
            }
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'User terpilih berhasil dihapus.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $userId        = $user?->id;
        $role          = $request->input('role');
        $needsEmployee = in_array($role, $this->employeeRoles);
        $needsBranch   = !in_array($role, $this->noBranchRoles);

        return $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],

                'email' => [
                    'required', 'email', 'max:255',
                    Rule::unique('users', 'email')->ignore($userId),
                ],

                'password' => [
                    $user ? 'nullable' : 'required',
                    'string', 'min:6', 'confirmed',
                ],

                'role' => [
                    'required', 'string',
                    Rule::in($this->allowedRoles),
                    Rule::exists('roles', 'name'),
                ],

                'employee_id' => [
                    // Required only for teknisi/kenek, optional for marketing
                    in_array($role, $this->staffRoles) ? 'required' : 'nullable',
                    'nullable', 'integer',
                    Rule::exists('employees', 'id'),
                ],

                'branch_ids' => [
                    $needsBranch ? 'required' : 'nullable',
                    'array',
                    $needsBranch ? 'min:1' : 'min:0',
                ],

                'branch_ids.*' => [
                    'integer', 'distinct',
                    Rule::exists('branches', 'id'),
                ],
            ],
            [
                'name.required'         => 'Nama user wajib diisi.',
                'email.required'        => 'Email wajib diisi.',
                'email.email'           => 'Format email tidak valid.',
                'email.unique'          => 'Email sudah digunakan.',
                'password.required'     => 'Password wajib diisi.',
                'password.min'          => 'Password minimal 6 karakter.',
                'password.confirmed'    => 'Konfirmasi password tidak sesuai.',
                'role.required'         => 'Role wajib dipilih.',
                'role.in'               => 'Role tidak valid.',
                'employee_id.required'  => 'Karyawan wajib dipilih untuk role teknisi/kenek.',
                'employee_id.exists'    => 'Karyawan yang dipilih tidak valid.',
                'branch_ids.required'   => 'Minimal pilih 1 cabang.',
                'branch_ids.array'      => 'Format cabang tidak valid.',
                'branch_ids.min'        => 'Minimal pilih 1 cabang.',
                'branch_ids.*.exists'   => 'Cabang yang dipilih tidak valid.',
                'branch_ids.*.distinct' => 'Cabang tidak boleh duplikat.',
            ]
        );
    }
}
