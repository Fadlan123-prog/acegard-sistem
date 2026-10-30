<?php

namespace App\Http\Controllers;

use App\Exports\CommissionExport;
use App\Jobs\GenerateExportJob;
use App\Models\ExportRequest;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\Employee;
use App\Repositories\CommissionRepository;
use App\Services\BranchContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class CommissionController extends Controller
{
    public function __construct(
        private BranchContext        $branch,
        private CommissionRepository $repo,
    ) {}

    public function index(Request $request)
    {
        $branchId = $this->branch->idOrNull();
        $branch   = $branchId ? \App\Models\Branch::find($branchId) : null;

        $user = auth()->user();

        // Teknisi / kenek / marketing hanya melihat komisi mereka sendiri
        $forcedEmployeeId = null;
        if ($user->employee && $user->hasAnyRole(['teknisi', 'kenek', 'marketing'])) {
            $forcedEmployeeId = $user->employee->id;
        }

        $filters = [
            'employee_id' => $forcedEmployeeId ?? $request->input('employee_id'),
            'role'        => $request->input('role'),
            'from'        => $request->input('from'),
            'to'          => $request->input('to'),
            'sort'        => $request->input('sort', 'date_desc'),
            'per_page'    => (int) $request->input('per_page', 20),
            'source_type' => $request->input('source_type'),
        ];

        $commissions   = $this->repo->paginated($filters, $branchId);
        $pageTotal     = $commissions->sum('amount');
        $totalFiltered = $this->repo->totalAmount($filters, $branchId);
        $roleSummary   = $this->repo->roleSummary($filters, $branchId);
        $employees     = $this->repo->employeeList($branchId);

        // Top leaderboards — cached for 5 min on the unfiltered hot path.
        $noDateFilter = empty($filters['from']) && empty($filters['to']);
        $cacheKey     = fn(string $type) => "commission.top_{$type}." . ($branchId ?? 0);
        $from         = $filters['from'] ?? null;
        $to           = $filters['to']   ?? null;

        $topMarketing = $noDateFilter
            ? Cache::remember($cacheKey('marketing'), 300,
                fn() => $this->repo->topByRoles(['marketing', 'adit_override', 'admin'], $branchId))
            : $this->repo->topByRoles(['marketing', 'adit_override', 'admin'], $branchId, $from, $to);

        $topInstaller = $noDateFilter
            ? Cache::remember($cacheKey('installer'), 300,
                fn() => $this->repo->topByRoles(['installer', 'teknisi', 'kenek', 'detailer'], $branchId))
            : $this->repo->topByRoles(['installer', 'teknisi', 'kenek', 'detailer'], $branchId, $from, $to);

        $sourceTypes = \App\Repositories\CommissionRepository::sourceTypes();

        return view('commission.index', compact(
            'commissions', 'pageTotal', 'totalFiltered', 'filters',
            'roleSummary', 'employees', 'branch', 'topMarketing', 'topInstaller',
            'sourceTypes'
        ));
    }

    /**
     * teknisi/kenek/marketing may only ever see/export/print their own
     * commissions — index() already enforces this; export/print/moveForm
     * did not (audit findings M-6/M-7).
     */
    private function forcedEmployeeId(): ?int
    {
        $user = auth()->user();
        if ($user->employee && $user->hasAnyRole(['teknisi', 'kenek', 'marketing'])) {
            return $user->employee->id;
        }
        return null;
    }

    public function exportByEmployee(Request $request)
    {
        $forcedEmployeeId = $this->forcedEmployeeId();
        if ($forcedEmployeeId) {
            $request->merge(['employee_id' => $forcedEmployeeId]);
        }

        $filters = [
            'branch_id'   => $this->branch->idOrNull(),
            'employee_id' => $request->input('employee_id'),
            'from'        => $request->input('from'),
            'to'          => $request->input('to'),
            'sort'        => $request->input('sort', 'date_desc'),
        ];

        // Nama file
        $suffix = [];
        if (!empty($filters['employee_id'])) {
            $empName = DB::table('employees')->where('id', $filters['employee_id'])->value('name');
            $suffix[] = \Str::slug($empName ?? 'employee');
        } else {
            $suffix[] = 'all';
        }
        if (!empty($filters['from'])) $suffix[] = $filters['from'];
        if (!empty($filters['to']))   $suffix[] = $filters['to'];

        $fileName = 'commissions_'.implode('_', $suffix).'_'.now()->format('Ymd_His').'.xlsx';

        $exportRequest = ExportRequest::create([
            'type'      => 'commission_excel',
            'params'    => ['filters' => $filters],
            'file_name' => $fileName,
            'status'    => 'pending',
            'user_id'   => auth()->id(),
            'branch_id' => $filters['branch_id'] ?? null,
        ]);

        GenerateExportJob::dispatch($exportRequest->id);

        return redirect()->route('export-status.show', $exportRequest->id);
    }

    public function moveForm(Commission $commission)
{
    abort_unless(
        auth()->user()->hasAnyRole(['superadmin', 'owner', 'admin', 'manager_operasional']),
        403
    );

    $user = auth()->user();
    if (!$user->hasRole('superadmin') && $commission->branch_id) {
        $allowed = $user->branches()->pluck('branches.id')->all();
        abort_unless(in_array((int) $commission->branch_id, $allowed, true), 403, 'Tidak boleh mengubah komisi cabang lain.');
    }

    $commission->load([
        'employee',
        'customer',
        'invoice',
    ]);

    $branchId  = $this->branch->idOrNull();

    $employees = Employee::query()
        ->where('is_active', 1)
        ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
        ->orderBy('name')
        ->get();

    return view('commission.move', compact('commission', 'employees'));
}

public function moveUpdate(Request $request, Commission $commission)
{
    abort_unless(
        auth()->user()->hasAnyRole(['superadmin', 'owner', 'admin', 'manager_operasional']),
        403
    );

    $user = auth()->user();
    if (!$user->hasRole('superadmin') && $commission->branch_id) {
        $allowed = $user->branches()->pluck('branches.id')->all();
        abort_unless(in_array((int) $commission->branch_id, $allowed, true), 403, 'Tidak boleh mengubah komisi cabang lain.');
    }

    $data = $request->validate([
        'employees_id'   => ['required', 'exists:employees,id'],
        'update_source'  => ['nullable', 'boolean'],
        'correction_note'=> ['nullable', 'string', 'max:1000'],
    ]);

    // Komisi yang sudah dibayar / masuk pencairan tidak boleh dipindah (riwayat uang).
    if ($commission->status === 'paid' || $commission->commission_payment_batch_id !== null) {
        return back()->with('error', 'Komisi ini sudah dibayarkan / masuk pencairan, tidak bisa dipindahkan.');
    }

    $targetBranchId = Employee::where('id', $data['employees_id'])->value('branch_id');
    abort_unless($user->canAccessBranch($targetBranchId !== null ? (int) $targetBranchId : null), 403, 'Karyawan tujuan bukan dari cabang Anda.');

    DB::beginTransaction();

    try {
        $commission->load(['employee', 'customer']);

        $oldEmployeeId = $commission->employees_id;
        $oldEmployeeName = $commission->employee->name ?? '-';

        $newEmployee = Employee::findOrFail($data['employees_id']);

        /*
        |--------------------------------------------------------------------------
        | 1. Update pemilik komisi
        |--------------------------------------------------------------------------
        */
        $commission->update([
            'employees_id' => $newEmployee->id,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 2. Update sumber data customer mobil jika role teknisi / kenek
        |--------------------------------------------------------------------------
        | Ini penting supaya data customer tidak tetap salah.
        */
        if ($request->boolean('update_source') && $commission->customer_id) {
            $roleText = strtolower(
                ($commission->role ?? '') . ' ' . ($commission->employee->job_position ?? '')
            );

            if (str_contains($roleText, 'teknisi') || str_contains($roleText, 'tukang')) {
                Customer::where('id', $commission->customer_id)
                    ->update([
                        'tukang_id' => $newEmployee->id,
                    ]);
            }

            if (str_contains($roleText, 'kenek')) {
                Customer::where('id', $commission->customer_id)
                    ->update([
                        'kenek_id' => $newEmployee->id,
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Simpan log koreksi jika table tersedia
        |--------------------------------------------------------------------------
        */
        if (Schema::hasTable('commission_corrections')) {
            DB::table('commission_corrections')->insert([
                'commission_id'     => $commission->id,
                'old_employee_id'   => $oldEmployeeId,
                'new_employee_id'   => $newEmployee->id,
                'amount'            => $commission->amount,
                'reason'            => $data['correction_note'] ?? null,
                'changed_by'        => auth()->id(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        DB::commit();

        return redirect()
            ->route('commission.index')
            ->with('success', "Komisi berhasil dipindahkan dari {$oldEmployeeName} ke {$newEmployee->name}.");

    } catch (\Throwable $e) {
        DB::rollBack();

        return back()
            ->withInput()
            ->with('error', 'Gagal memindahkan komisi: ' . $e->getMessage());
    }
}

public function toggleActive(Commission $commission)
{
    $user = auth()->user();
    if (!$user->hasRole('superadmin') && !$user->hasRole('admin')) {
        abort(403, 'Tidak punya akses untuk mengubah status komisi.');
    }

    if (!$user->hasRole('superadmin') && $commission->branch_id) {
        $allowed = $user->branches()->pluck('branches.id')->all();
        abort_unless(in_array((int) $commission->branch_id, $allowed, true), 403, 'Tidak boleh mengubah komisi cabang lain.');
    }

    if (!in_array($commission->role, ['marketing', 'adit_override'])) {
        return back()->with('error', 'Toggle aktif hanya tersedia untuk komisi marketing.');
    }

    if ($commission->status === 'paid' || $commission->commission_payment_batch_id !== null) {
        return back()->with('error', 'Komisi yang sudah dibayarkan / masuk pencairan tidak bisa dinonaktifkan.');
    }

    $was = (bool) $commission->is_active;
    $commission->update(['is_active' => !$was]);

    if (Schema::hasTable('commission_corrections')) {
        DB::table('commission_corrections')->insert([
            'commission_id'   => $commission->id,
            'old_employee_id' => $commission->employees_id,
            'new_employee_id' => $commission->employees_id,
            'amount'          => $commission->amount,
            'reason'          => ($was ? 'Dinonaktifkan' : 'Diaktifkan kembali') . ' oleh ' . auth()->user()->name,
            'changed_by'      => auth()->id(),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    $label = $commission->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';
    return back()->with('success', "Komisi berhasil {$label}.");
}

public function print(Request $request)
{
    $forcedEmployeeId = $this->forcedEmployeeId();
    if ($forcedEmployeeId) {
        $request->merge(['employee_id' => $forcedEmployeeId]);
    }

    $data = $request->validate([
        'employee_id' => ['required', 'exists:employees,id'],
        'from'        => ['required', 'date'],
        'to'          => ['required', 'date'],
        'kasbon'      => ['nullable', 'numeric', 'min:0'],
        'uang_motor'  => ['nullable', 'numeric', 'min:0'],
        'uang_makan'  => ['nullable', 'numeric', 'min:0'],
    ]);

    $employee  = Employee::findOrFail($data['employee_id']);
    abort_unless(
        auth()->user()->canAccessBranch($employee->branch_id !== null ? (int) $employee->branch_id : null)
        || (int) $employee->id === (int) $forcedEmployeeId,
        403,
        'Karyawan ini bukan dari cabang Anda.'
    );
    $from      = Carbon::parse($data['from'])->startOfDay();
    $to        = Carbon::parse($data['to'])->endOfDay();
    $kasbon    = (int) ($data['kasbon']     ?? 0);
    $uangMotor = (int) ($data['uang_motor'] ?? 0);
    $uangMakan = (int) ($data['uang_makan'] ?? 0);

    $rows        = $this->repo->forEmployee($employee->id, $from, $to);
    $totalKomisi = (int) $rows->sum('amount');
    $grandTotal  = $totalKomisi - $kasbon + $uangMotor + $uangMakan;

    return view('commission.print', compact(
        'employee',
        'rows',
        'from',
        'to',
        'kasbon',
        'uangMotor',
        'uangMakan',
        'totalKomisi',
        'grandTotal'
    ));
}

}
