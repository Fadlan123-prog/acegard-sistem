<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\SalesOrder;
use App\Models\Commission;
use App\Models\User;
use App\Notifications\JobAssigned;
use App\Services\BranchContext;
use App\Services\DashboardCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkOrderController extends Controller
{
    private const SUPERVISOR_ROLES = ['superadmin', 'admin', 'owner', 'manager_operasional'];

    public function __construct(
        private BranchContext $branch,
        private DashboardCacheService $cache,
    ) {}

    public function index(Request $request)
    {
        $user          = $request->user();
        $activeBranch  = $this->branch->idOrNull();
        $isSuperadmin  = $user->hasRole('superadmin');

        $query = Customer::query()
            ->with([
                'invoice',
                'tukang:id,name',
                'kenek:id,name',
                'branch:id,name,city',
            ])
            ->whereHas('invoice');

        if (!$isSuperadmin) {
            $query->where('branch_id', $activeBranch);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by work status
        $workStatus = $request->get('work_status', 'active');
        if ($workStatus === 'active') {
            $query->whereHas('invoice', fn($q) => $q->whereIn('work_status', ['pending', 'accepted']));
        } elseif ($workStatus === 'completed') {
            $query->whereHas('invoice', fn($q) => $q->where('work_status', 'completed'));
        } elseif ($workStatus === 'pending_qc') {
            $query->whereHas('invoice', fn($q) => $q->where('work_status', 'completed')->where('qc_status', 'pending'));
        } elseif ($workStatus === 'unassigned') {
            $query->whereNull('tukang_id');
        }

        // Filter by technician
        if ($request->filled('tukang_id')) {
            $query->where('tukang_id', $request->tukang_id);
        }

        $customers = $query->latest()->paginate(20)->withQueryString();

        // Customer yang berasal dari SO: relasinya tersimpan di SO (converted_to_*).
        $sourceSalesOrders = SalesOrder::query()
            ->where('converted_to_type', Customer::class)
            ->whereIn('converted_to_id', $customers->pluck('id'))
            ->get(['id', 'so_number', 'converted_to_id'])
            ->keyBy('converted_to_id');

        $branches  = $isSuperadmin ? Branch::orderBy('name')->get(['id', 'name', 'city']) : collect();
        $employees = Employee::where('is_active', true)->orderBy('name')->get(['id', 'name', 'job_position', 'branch_id']);

        $pendingQcQuery = Customer::query()->whereHas('invoice', fn($q) => $q->where('work_status', 'completed')->where('qc_status', 'pending'));
        if (!$isSuperadmin) {
            $pendingQcQuery->where('branch_id', $activeBranch);
        }

        return view('work-orders.index', [
            'title'         => 'Monitoring Pekerjaan & QC',
            'customers'     => $customers,
            'sourceSalesOrders' => $sourceSalesOrders,
            'branches'      => $branches,
            'employees'     => $employees,
            'isSuperadmin'  => $isSuperadmin,
            'isSupervisor'  => $user->hasAnyRole(self::SUPERVISOR_ROLES),
            'pendingQcCount'=> $pendingQcQuery->count(),
            'filters'       => [
                'branch_id'   => $request->branch_id,
                'work_status' => $workStatus,
                'tukang_id'   => $request->tukang_id,
            ],
        ]);
    }

    public function assign(Request $request, Customer $customer)
    {
        $branchId = $this->branch->idOrNull();
        abort_unless($branchId === null || (int) $customer->branch_id === (int) $branchId, 403);

        // Normalize empty strings from form to null
        $request->merge([
            'tukang_id' => $request->input('tukang_id') ?: null,
            'kenek_id'  => $request->input('kenek_id')  ?: null,
        ]);

        $validated = $request->validate(
            [
                'tukang_id' => ['nullable', 'integer', Rule::exists('employees', 'id')->where('branch_id', $customer->branch_id)],
                'kenek_id'  => ['nullable', 'integer', Rule::exists('employees', 'id')->where('branch_id', $customer->branch_id)],
            ],
            [
                'tukang_id.exists' => 'Teknisi yang dipilih tidak valid atau bukan dari cabang customer ini.',
                'kenek_id.exists'  => 'Kenek yang dipilih tidak valid atau bukan dari cabang customer ini.',
            ]
        );

        $invoice = $customer->invoice;

        if ($invoice?->work_status === 'completed') {
            return back()->with('error', 'Tidak bisa mengubah penugasan — pekerjaan sudah selesai.');
        }

        $oldTukangId = $customer->tukang_id ? (int) $customer->tukang_id : null;
        $oldKenekId  = $customer->kenek_id  ? (int) $customer->kenek_id  : null;
        $newTukangId = $validated['tukang_id'] ? (int) $validated['tukang_id'] : null;
        $newKenekId  = $validated['kenek_id']  ? (int) $validated['kenek_id']  : null;

        $tukangChanged = $oldTukangId !== $newTukangId;
        $kenekChanged  = $oldKenekId  !== $newKenekId;

        if (!$tukangChanged && !$kenekChanged) {
            return back()->with('info', 'Penugasan tidak berubah.');
        }

        DB::transaction(function () use (
            $customer, $invoice,
            $oldTukangId, $oldKenekId,
            $newTukangId, $newKenekId,
            $tukangChanged, $kenekChanged
        ) {
            if ($invoice) {
                // Cancel pending/eligible commissions for roles being replaced
                $rolesToCancel = [];
                if ($tukangChanged && $oldTukangId) {
                    $rolesToCancel[] = ['employees_id' => $oldTukangId, 'role' => 'teknisi'];
                }
                if ($kenekChanged && $oldKenekId) {
                    $rolesToCancel[] = ['employees_id' => $oldKenekId, 'role' => 'kenek'];
                }

                foreach ($rolesToCancel as $target) {
                    DB::table('commissions')
                        ->where('invoice_id', $invoice->id)
                        ->where('employees_id', $target['employees_id'])
                        ->where('role', $target['role'])
                        ->whereIn('status', ['pending', 'eligible'])
                        ->update(['status' => 'cancelled', 'updated_at' => now()]);
                }

                // Teknisi berubah → reset status ke pending agar teknisi baru perlu terima order
                if ($tukangChanged && $invoice->work_status === 'accepted') {
                    $invoice->update([
                        'work_status' => 'pending',
                        'accepted_at' => null,
                    ]);
                }
            }

            // Update assignment (null = unassign)
            $customer->update([
                'tukang_id' => $newTukangId,
                'kenek_id'  => $newKenekId,
            ]);

        });

        // Kirim notifikasi web ke teknisi baru (jika ada user yang terhubung ke employee)
        if ($newTukangId && $tukangChanged) {
            $assignedUser = User::where('employee_id', $newTukangId)->first();
            if ($assignedUser) {
                $customer->load(['invoice', 'branch:id,name']);
                $assignedUser->notify(new JobAssigned($customer));
            }
        }

        $message = $newTukangId
            ? 'Penugasan diperbarui. Teknisi baru perlu menerima order sebelum mulai bekerja.'
            : 'Teknisi dilepas dari order ini. Order kembali ke status Pending.';

        return back()->with('success', $message);
    }

    public function qcApprove(Request $request, Customer $customer)
    {
        abort_unless(auth()->user()->hasAnyRole(self::SUPERVISOR_ROLES), 403, 'Hanya supervisor yang dapat menyetujui QC.');
        $branchId = $this->branch->idOrNull();
        abort_unless($branchId === null || (int) $customer->branch_id === (int) $branchId, 403);

        $invoice = $customer->invoice;

        if (!$invoice || !$invoice->isQcBlocking()) {
            return back()->with('error', 'Invoice ini tidak sedang menunggu QC.');
        }

        $invoice->update([
            'qc_status'      => 'passed',
            'qc_notes'       => null,
            'qc_reviewed_by' => auth()->id(),
            'qc_reviewed_at' => now(),
        ]);

        return back()->with('success', 'QC disetujui. Invoice sudah bisa dilunasi.');
    }

    public function qcReject(Request $request, Customer $customer)
    {
        abort_unless(auth()->user()->hasAnyRole(self::SUPERVISOR_ROLES), 403, 'Hanya supervisor yang dapat menolak QC.');
        $branchId = $this->branch->idOrNull();
        abort_unless($branchId === null || (int) $customer->branch_id === (int) $branchId, 403);

        $data = $request->validate([
            'qc_notes' => ['required', 'string', 'max:1000'],
        ], [
            'qc_notes.required' => 'Catatan perbaikan wajib diisi agar teknisi tahu apa yang harus diperbaiki.',
        ]);

        $invoice = $customer->invoice;

        if (!$invoice || !$invoice->isQcBlocking()) {
            return back()->with('error', 'Invoice ini tidak sedang menunggu QC.');
        }

        $invoice->update([
            'qc_status'      => 'failed',
            'qc_notes'       => $data['qc_notes'],
            'qc_reviewed_by' => auth()->id(),
            'qc_reviewed_at' => now(),
            'work_status'    => 'accepted',
            'completed_at'   => null,
        ]);

        // Send the rework back to the technician's active job list
        $customer->loadMissing('tukang', 'kenek');
        if ($customer->tukang_id) {
            $this->cache->invalidateTeknisi($customer->tukang_id);
        }
        if ($customer->kenek_id) {
            $this->cache->invalidateTeknisi($customer->kenek_id);
        }

        return back()->with('success', 'QC ditolak. Pekerjaan dikembalikan ke teknisi untuk diperbaiki.');
    }
}
