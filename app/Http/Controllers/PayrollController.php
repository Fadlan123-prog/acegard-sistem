<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\ExportRequest;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\SalaryAdjustment;
use App\Domain\HR\OffQuotaPolicy;
use App\Jobs\ProcessPayrollJob;
use App\Services\BranchContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PayrollController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Payroll::with('branch')
            ->withCount('items')
            ->withSum('items as total_monthly_salary', 'monthly_salary')
            ->withSum('items as total_prorate_salary', 'prorate_salary')
            ->withSum('items as total_allowance', 'transport_allowance')
            ->withSum('items as total_meal_allowance', 'meal_allowance')
            ->withSum('items as total_deduction', 'attendance_deduction')
            ->withSum('items as total_rounded_salary', 'rounded_salary');

        if ($user->hasRole('superadmin')) {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
        } else {
            $query->where('branch_id', $this->branch->id());
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payrolls = $query
            ->latest('month')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $branches = $user->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : $user->branches()->orderBy('name')->get();

        return view('payrolls.index', compact('payrolls', 'branches'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();

        $branches = $user->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : $user->branches()->orderBy('name')->get();

        $month = $request->input('month', now()->format('Y-m'));

        $branchId = $request->input('branch_id');

        if (!$branchId) {
            $branchId = $user->hasRole('superadmin')
                ? optional($branches->first())->id
                : $this->branch->idOrNull();
        }

        $workingDays = (int) $request->input('working_days', 26);

        return view('payrolls.create', compact(
            'branches',
            'month',
            'branchId',
            'workingDays'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'month' => ['required', 'date_format:Y-m'],
            'working_days' => ['required', 'integer', 'min:1'],
            'regenerate' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->ensureBranchAccess((int) $data['branch_id']);

        // Early guard: block if existing approved/paid payroll
        $existingPayroll = Payroll::where('branch_id', $data['branch_id'])
            ->where('month', $data['month'])
            ->first();

        if ($existingPayroll && !$request->boolean('regenerate')) {
            return redirect()
                ->route('payrolls.show', $existingPayroll->id)
                ->with('error', 'Payroll bulan ini sudah pernah dibuat. Aktifkan regenerate untuk membuat ulang.');
        }

        if ($existingPayroll && $existingPayroll->status !== 'draft') {
            return redirect()
                ->route('payrolls.show', $existingPayroll->id)
                ->with('error', 'Payroll yang sudah approved/paid tidak dapat dibuat ulang.');
        }

        $exportRequest = ExportRequest::create([
            'type'      => 'payroll_generate',
            'params'    => [
                'branch_id'    => (int) $data['branch_id'],
                'month'        => $data['month'],
                'working_days' => (int) $data['working_days'],
                'generated_by' => auth()->id(),
                'notes'        => $data['notes'] ?? null,
                'regenerate'   => $request->boolean('regenerate'),
            ],
            'status'    => 'pending',
            'user_id'   => auth()->id(),
            'branch_id' => (int) $data['branch_id'],
        ]);

        ProcessPayrollJob::dispatch($exportRequest->id);

        return redirect()->route('export-status.show', $exportRequest->id);
    }

    public function show(Payroll $payroll)
    {
        $this->ensurePayrollAccess($payroll);

        $payroll->load([
            'branch',
            'items.employee',
        ]);

        return view('payrolls.show', compact('payroll'));
    }

    public function edit(Payroll $payroll)
    {
        $this->ensurePayrollAccess($payroll);

        if ($payroll->status !== 'draft') {
            return redirect()
                ->route('payrolls.show', $payroll->id)
                ->with('error', 'Payroll yang sudah approved/paid tidak bisa diedit.');
        }

        $payroll->load([
            'branch',
            'items.employee',
        ]);

        return view('payrolls.edit', compact('payroll'));
    }

    public function update(Request $request, Payroll $payroll)
    {
        $this->ensurePayrollAccess($payroll);

        if ($payroll->status !== 'draft') {
            return redirect()
                ->route('payrolls.show', $payroll->id)
                ->with('error', 'Payroll yang sudah approved/paid tidak bisa diedit.');
        }

        $data = $request->validate([
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:payroll_items,id'],

            'items.*.transport_allowance' => ['nullable', 'integer', 'min:0'],
            'items.*.meal_allowance' => ['nullable', 'integer', 'min:0'],

            'items.*.main_deduction' => ['nullable', 'integer', 'min:0'],
            'items.*.cash_advance' => ['nullable', 'integer', 'min:0'],
            'items.*.attendance_deduction' => ['nullable', 'integer', 'min:0'],
            'items.*.other_deduction' => ['nullable', 'integer', 'min:0'],

            'items.*.notes' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();

        try {
            $payroll->update([
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $row) {
                $item = PayrollItem::where('payroll_id', $payroll->id)
                    ->where('id', $row['id'])
                    ->firstOrFail();

                $transportAllowance = (int) ($row['transport_allowance'] ?? 0);
                $mealAllowance = (int) ($row['meal_allowance'] ?? 0);

                $mainDeduction = (int) ($row['main_deduction'] ?? 0);
                $cashAdvance = (int) ($row['cash_advance'] ?? 0);
                $attendanceDeduction = (int) ($row['attendance_deduction'] ?? 0);
                $otherDeduction = (int) ($row['other_deduction'] ?? 0);

                $totalSalary =
                    (int) $item->prorate_salary
                    + $transportAllowance
                    + $mealAllowance
                    - $mainDeduction
                    - $cashAdvance
                    - $attendanceDeduction
                    - $otherDeduction;

                $totalSalary = max(0, $totalSalary);
                $roundedSalary = $this->roundToThousand($totalSalary);

                $item->update([
                    'transport_allowance' => $transportAllowance,
                    'meal_allowance' => $mealAllowance,

                    'main_deduction' => $mainDeduction,
                    'cash_advance' => $cashAdvance,
                    'attendance_deduction' => $attendanceDeduction,
                    'other_deduction' => $otherDeduction,

                    'total_salary' => $totalSalary,
                    'rounded_salary' => $roundedSalary,

                    'notes' => $row['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('payrolls.show', $payroll->id)
                ->with('success', 'Payroll berhasil diperbarui.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Gagal update payroll: ' . $e->getMessage());
        }
    }

    public function approve(Payroll $payroll)
    {
        $this->ensurePayrollAccess($payroll);

        if ($payroll->status !== 'draft') {
            return back()->with('error', 'Hanya payroll draft yang bisa di-approve.');
        }

        $payroll->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Payroll berhasil di-approve.');
    }

    public function markPaid(Payroll $payroll)
    {
        $this->ensurePayrollAccess($payroll);

        if ($payroll->status !== 'approved') {
            return back()->with('error', 'Hanya payroll approved yang bisa ditandai paid.');
        }

        $payroll->update([
            'status' => 'paid',
        ]);

        return back()->with('success', 'Payroll berhasil ditandai paid.');
    }

    public function downloadSlip(Payroll $payroll, int $itemId)
    {
        $this->ensurePayrollAccess($payroll);

        $item = PayrollItem::where('id', $itemId)
            ->where('payroll_id', $payroll->id)
            ->firstOrFail();

        $item->load('employee');
        $branch = $payroll->branch;
        $period = Carbon::parse($payroll->month . '-01')->isoFormat('MMMM Y');

        $pdf = Pdf::loadView('payrolls.slip-gaji', compact('payroll', 'item', 'branch', 'period'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled'      => false,
                'isHtml5ParserEnabled' => true,
                'defaultFont'          => 'Poppins',
                'fontDir'              => storage_path('fonts'),
                'fontCache'            => storage_path('fonts'),
            ]);

        $bulan    = Carbon::parse($payroll->month . '-01')->isoFormat('MMMM-YYYY');
        $filename = 'slip-gaji-' . str($item->employee_name)->slug() . '-' . $bulan . '.pdf';

        return $pdf->download($filename);
    }

    public function downloadSlipAll(Payroll $payroll)
    {
        $this->ensurePayrollAccess($payroll);

        $payroll->load('items.employee', 'branch');
        $branch = $payroll->branch;
        $period = Carbon::parse($payroll->month . '-01')->isoFormat('MMMM Y');

        $pdf = Pdf::loadView('payrolls.slip-gaji-all', compact('payroll', 'branch', 'period'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled'      => false,
                'isHtml5ParserEnabled' => true,
                'defaultFont'          => 'Poppins',
                'fontDir'              => storage_path('fonts'),
                'fontCache'            => storage_path('fonts'),
            ]);

        $bulan    = Carbon::parse($payroll->month . '-01')->isoFormat('MMMM-YYYY');
        $filename = 'slip-gaji-semua-' . $bulan . '-' . str($branch->name ?? 'cabang')->slug() . '.pdf';

        return $pdf->download($filename);
    }

    public function destroy(Payroll $payroll)
    {
        $this->ensurePayrollAccess($payroll);

        // if ($payroll->status === 'paid') {
        //     return back()->with('error', 'Payroll yang sudah paid tidak bisa dihapus.');
        // }

        DB::beginTransaction();

        try {
            PayrollItem::where('payroll_id', $payroll->id)->delete();
            $payroll->delete();

            DB::commit();

            return redirect()
                ->route('payrolls.index')
                ->with('success', 'Payroll berhasil dihapus.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal hapus payroll: ' . $e->getMessage());
        }
    }

    protected function buildPayrollItemData(
        Payroll $payroll,
        Employee $employee,
        $attendanceRows,
        int $workingDays,
        ?SalaryAdjustment $adjustment = null
    ): array {
        $present = $attendanceRows->where('status', 'present')->count();
        $off = $attendanceRows->where('status', 'off')->count();
        $sick = $attendanceRows->where('status', 'sick')->count();
        $permission = $attendanceRows->where('status', 'permission')->count();
        $absent = $attendanceRows->where('status', 'absent')->count();

        $offQuota  = $this->getMonthlyOffQuota($employee->job_position);
        $paidOff   = min($off, $offQuota);
        $excessOff = max(0, $off - $offQuota);

        $monthlySalary = (int) ($employee->monthly_salary ?? 0);
        // Gunakan daysInMonth - offQuota sesuai formula laporan gaji
        $startDate    = Carbon::parse($payroll->month . '-01');
        $daysInMonth  = $startDate->daysInMonth;
        $effectiveDays = max(1, $daysInMonth - $offQuota);
        $dailyProrate  = $monthlySalary / $effectiveDays;
        $workingDays   = $effectiveDays; // override agar konsisten dengan attendance report

        /*
        |--------------------------------------------------------------------------
        | Paid Days
        |--------------------------------------------------------------------------
        | Konsep ini membayar:
        | - Hari masuk
        | - Libur sesuai jatah
        | - Sakit
        | - Izin
        |
        | Kalau ingin mengikuti Excel lama yang hanya menghitung dari hari masuk,
        | ganti baris ini menjadi:
        | $paidDays = $present;
        */
        $paidDays = $present + $paidOff + $sick + $permission;

        // Gaji dasar: rate × (masuk + sakit) — formula sama dengan attendance report
        $prorateSalary = (int) round($dailyProrate * ($present + $sick));

        // Tambahan & potongan dari SalaryAdjustment
        $transportAllowance  = (int) ($adjustment?->uang_motor      ?? 0);
        $mealAllowance       = (int) ($adjustment?->uang_makan      ?? 0);
        $mainDeduction       = (int) ($adjustment?->potongan_rokok  ?? 0);
        $cashAdvance         = (int) ($adjustment?->kasbon          ?? 0);
        $attendanceDeduction = (int) ($adjustment?->potongan_absen  ?? 0);

        $totalSalary = max(0,
            $prorateSalary
            + $transportAllowance
            + $mealAllowance
            - $mainDeduction
            - $cashAdvance
            - $attendanceDeduction
        );

        $roundedSalary = $this->roundToThousand($totalSalary);

        return [
            'payroll_id'   => $payroll->id,
            'employee_id'  => $employee->id,
            'employee_name' => $employee->name,
            'job_position' => $employee->job_position,

            'monthly_salary' => $monthlySalary,
            'daily_prorate'  => $dailyProrate,

            'present_days'    => $present,
            'off_days'        => $off,
            'off_quota'       => $offQuota,
            'paid_off_days'   => $paidOff,
            'excess_off_days' => $excessOff,
            'sick_days'       => $sick,
            'permission_days' => $permission,
            'absent_days'     => $absent,

            'paid_days'      => $present + $sick,
            'prorate_salary' => $prorateSalary,

            'transport_allowance'  => $transportAllowance,
            'meal_allowance'       => $mealAllowance,
            'main_deduction'       => $mainDeduction,
            'cash_advance'         => $cashAdvance,
            'attendance_deduction' => $attendanceDeduction,
            'other_deduction'      => 0,

            'total_salary'   => $totalSalary,
            'rounded_salary' => $roundedSalary,
            'notes'          => null,
        ];
    }

    public function refresh(Payroll $payroll)
    {
        $this->ensurePayrollAccess($payroll);

        if ($payroll->status !== 'draft') {
            return back()->with('error', 'Hanya payroll draft yang bisa direfresh.');
        }

        $exportRequest = ExportRequest::create([
            'type'      => 'payroll_refresh',
            'params'    => ['payroll_id' => $payroll->id],
            'status'    => 'pending',
            'user_id'   => auth()->id(),
            'branch_id' => $payroll->branch_id,
        ]);

        \App\Jobs\ProcessPayrollRefreshJob::dispatch($exportRequest->id);

        return redirect()->route('export-status.show', $exportRequest->id);
    }

    protected function getMonthlyOffQuota(?string $jobPosition): int
    {
        return OffQuotaPolicy::forPosition((string) $jobPosition);
    }

    protected function roundToThousand(int|float $amount): int
    {
        return (int) round($amount / 1000) * 1000;
    }

    protected function ensurePayrollAccess(Payroll $payroll): void
    {
        $this->ensureBranchAccess((int) $payroll->branch_id);
    }

    protected function ensureBranchAccess(int $branchId): void
    {
        $user = auth()->user();

        if ($user->hasRole('superadmin')) {
            return;
        }

        $allowedBranchIds = $user->branches
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        if (!$allowedBranchIds->contains((int) $branchId)) {
            abort(403, 'Tidak boleh mengakses data cabang lain.');
        }
    }
}
