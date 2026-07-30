<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Services\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $search = trim((string) $request->get('search'));
        $branchId = $request->get('branch_id');
        $isActive = $request->get('is_active');

        $query = Employee::query()
            ->with('branch');

        /*
        |--------------------------------------------------------------------------
        | Filter cabang
        |--------------------------------------------------------------------------
        */
        if ($user->hasRole('superadmin')) {
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }
        } else {
            $activeBranchId = $this->getActiveBranchIdForUser($user);

            $query->where('branch_id', $activeBranchId);
        }

        /*
        |--------------------------------------------------------------------------
        | Search nama / posisi
        |--------------------------------------------------------------------------
        */
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('job_position', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter status aktif / tidak aktif
        |--------------------------------------------------------------------------
        */
        if ($isActive !== null && $isActive !== '') {
            $query->where('is_active', (int) $isActive);
        }

        $employees = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $branches = $this->getBranchesForUser($user);

        return view('employee.index', [
            'employees' => $employees,
            'branches'  => $branches,
            'filters'   => [
                'search'    => $search,
                'branch_id' => $branchId,
                'is_active' => $isActive,
            ],
        ]);
    }

    public function create(Request $request)
    {
        $branches = $this->getBranchesForUser($request->user());

        return view('employee.create', [
            'branches' => $branches,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateEmployee($request);

        $this->authorizeBranchAccess(
            $request,
            (int) $data['branch_id'],
            'Tidak boleh menambahkan karyawan ke cabang lain.'
        );

        $data = $this->normalizeEmployeeData($request, $data);

        Employee::create($data);

        return redirect()
            ->route('employee.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function show(Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        $employee->load('branch');

        return view('employee.show', [
            'employee' => $employee,
        ]);
    }

    public function edit(Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        $branches = $this->getBranchesForUser(request()->user());

        return view('employee.edit', [
            'employee' => $employee,
            'branches' => $branches,
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        $data = $this->validateEmployee($request, $employee);

        $this->authorizeBranchAccess(
            $request,
            (int) $data['branch_id'],
            'Tidak boleh memindahkan karyawan ke cabang lain.'
        );

        $data = $this->normalizeEmployeeData($request, $data, $employee);

        $employee->update($data);

        return redirect()
            ->route('employee.index')
            ->with('success', 'Karyawan berhasil diperbarui.');
    }

    public function toggle(Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        $employee->update(['is_active' => !$employee->is_active]);

        $label = $employee->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('employee.index', request()->only(['search', 'branch_id', 'is_active']))
            ->with('success', "Karyawan {$employee->name} berhasil {$label}.");
    }

    public function destroy(Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        $employee->delete();

        return redirect()
            ->route('employee.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }

    private function validateEmployee(Request $request, ?Employee $employee = null): array
    {
        return $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'phone_number' => [
                    'nullable',
                    'string',
                    'max:20',
                ],

                'job_position' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'branch_id' => [
                    'required',
                    'integer',
                    Rule::exists('branches', 'id'),
                ],

                'monthly_salary' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],

                'meal_allowance_enabled' => [
                    'nullable',
                    'boolean',
                ],

                'meal_allowance_rate' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],

                'is_active' => [
                    'nullable',
                    'boolean',
                ],

                // HR fields
                'npwp'              => ['nullable', 'string', 'max:20'],
                'hire_date'         => ['nullable', 'date'],
                'employment_status' => ['nullable', 'in:permanent,contract,probation'],

                // BPJS Ketenagakerjaan
                'bpjs_ketenagakerjaan_no'       => ['nullable', 'string', 'max:30'],
                'bpjs_ketenagakerjaan_enrolled' => ['nullable', 'boolean'],
                'jkk_risk_group'                => ['nullable', 'integer', 'min:1', 'max:5'],

                // BPJS Kesehatan
                'bpjs_kesehatan_no'       => ['nullable', 'string', 'max:30'],
                'bpjs_kesehatan_enrolled' => ['nullable', 'boolean'],
            ],
            [
                'name.required'        => 'Nama karyawan wajib diisi.',
                'branch_id.required'   => 'Cabang wajib dipilih.',
                'branch_id.exists'     => 'Cabang tidak valid.',
                'monthly_salary.min'   => 'Gaji bulanan tidak boleh minus.',
                'meal_allowance_rate.min' => 'Nominal uang makan tidak boleh minus.',
            ]
        );
    }

    private function normalizeEmployeeData(
        Request $request,
        array $data,
        ?Employee $employee = null
    ): array {
        $data['monthly_salary'] = (int) ($data['monthly_salary'] ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Status aktif
        |--------------------------------------------------------------------------
        | Kalau form menggunakan checkbox, sebaiknya tambahkan hidden input value 0.
        | Kalau field tidak dikirim, create default aktif, update mempertahankan nilai lama.
        */
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        } else {
            $data['is_active'] = $employee?->is_active ?? true;
        }

        /*
        |--------------------------------------------------------------------------
        | Uang makan
        |--------------------------------------------------------------------------
        | Kalau tidak dicentang, karyawan tidak dapat uang makan.
        | Kalau nominal khusus kosong, sistem pakai default dari jabatan:
        | Teknisi 50.000, Kenek 35.000, lainnya 0.
        */
        if ($request->has('meal_allowance_enabled')) {
            $data['meal_allowance_enabled'] = $request->boolean('meal_allowance_enabled');
        } else {
            $data['meal_allowance_enabled'] = $employee?->meal_allowance_enabled ?? true;
        }

        $data['meal_allowance_rate'] = $request->filled('meal_allowance_rate')
            ? (int) $request->input('meal_allowance_rate')
            : null;

        if (!$data['meal_allowance_enabled']) {
            $data['meal_allowance_rate'] = null;
        }

        // BPJS enrolled flags — checkbox pattern (hidden 0 + checkbox 1)
        $data['bpjs_ketenagakerjaan_enrolled'] = $request->boolean('bpjs_ketenagakerjaan_enrolled');
        $data['bpjs_kesehatan_enrolled']        = $request->boolean('bpjs_kesehatan_enrolled');

        if (!$data['bpjs_ketenagakerjaan_enrolled']) {
            $data['bpjs_ketenagakerjaan_no'] = null;
        }
        if (!$data['bpjs_kesehatan_enrolled']) {
            $data['bpjs_kesehatan_no'] = null;
        }

        $data['jkk_risk_group']   = (int) ($data['jkk_risk_group'] ?? 1) ?: 1;
        $data['employment_status'] = $data['employment_status'] ?? 'permanent';

        return $data;
    }

    private function authorizeEmployeeAccess(Employee $employee): void
    {
        $user = request()->user();

        if ($user->hasRole('superadmin')) {
            return;
        }

        $hasAccess = $user->branches()
            ->where('branches.id', $employee->branch_id)
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Tidak boleh mengakses karyawan cabang lain.');
        }
    }

    private function authorizeBranchAccess(
        Request $request,
        int $branchId,
        string $message
    ): void {
        $user = $request->user();

        if ($user->hasRole('superadmin')) {
            return;
        }

        $hasAccess = $user->branches()
            ->where('branches.id', $branchId)
            ->exists();

        if (!$hasAccess) {
            abort(403, $message);
        }
    }

    private function getBranchesForUser($user)
    {
        if ($user->hasRole('superadmin')) {
            return Branch::query()
                ->orderBy('name')
                ->get();
        }

        return $user->branches()
            ->orderBy('branches.name')
            ->get();
    }

    private function getActiveBranchIdForUser($user): int
    {
        $activeBranchId = $this->branch->idOrNull();

        if ($activeBranchId) {
            $hasAccess = $user->branches()
                ->where('branches.id', $activeBranchId)
                ->exists();

            if ($hasAccess) {
                return $activeBranchId;
            }
        }

        $defaultBranchId = $user->branches()
            ->orderBy('branches.id')
            ->value('branches.id');

        if (!$defaultBranchId) {
            abort(403, 'Akun ini belum memiliki akses cabang.');
        }

        $this->branch->switchTo((int) $defaultBranchId);

        return (int) $defaultBranchId;
    }
}
