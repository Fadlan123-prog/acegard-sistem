<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Commission;
use App\Models\CommissionPaymentBatch;
use App\Services\BranchContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionPeriodController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** Return [start, end] Carbon for the period a given date belongs to. */
    public static function periodFor(Carbon $date): array
    {
        if ($date->day >= 16) {
            $start = $date->copy()->setDay(16)->startOfDay();
            $end   = $date->copy()->addMonth()->setDay(15)->endOfDay();
        } else {
            $start = $date->copy()->subMonth()->setDay(16)->startOfDay();
            $end   = $date->copy()->setDay(15)->endOfDay();
        }
        return [$start, $end];
    }

    /** Current active period. */
    public static function currentPeriod(): array
    {
        return self::periodFor(now());
    }

    // ─── Actions ─────────────────────────────────────────────────────────────

    /** List all periods that have at least one commission. */
    public function index(Request $request)
    {
        $branchId = $this->branch->idOrNull();
        $branch   = $branchId ? Branch::find($branchId) : null;

        // Get distinct period_starts from commissions (computed in MySQL)
        $periodRows = DB::table('commissions as c')
            ->leftJoin('employees as e', 'e.id', '=', 'c.employees_id')
            ->when($branchId, fn($q) => $q->where('e.branch_id', $branchId))
            ->selectRaw("
                CASE
                    WHEN DAY(c.created_at) >= 16
                        THEN DATE(CONCAT(DATE_FORMAT(c.created_at, '%Y-%m'), '-16'))
                    ELSE
                        DATE(CONCAT(DATE_FORMAT(DATE_SUB(c.created_at, INTERVAL 1 MONTH), '%Y-%m'), '-16'))
                END AS period_start,
                COUNT(*)                                                           AS total_cnt,
                SUM(c.amount)                                                      AS total_amount,
                SUM(CASE WHEN c.status = 'paid' THEN c.amount ELSE 0 END)         AS paid_amount,
                SUM(CASE WHEN c.status != 'paid' THEN c.amount ELSE 0 END)        AS unpaid_amount,
                COUNT(CASE WHEN c.status != 'paid' THEN 1 END)                    AS unpaid_cnt,
                COUNT(CASE WHEN c.status  = 'paid' THEN 1 END)                    AS paid_cnt
            ")
            ->groupByRaw('period_start')
            ->orderByRaw('period_start DESC')
            ->get();

        // Enrich with period_end and status label
        $periods = $periodRows->map(function ($row) {
            $start  = Carbon::parse($row->period_start);
            $end    = $start->copy()->addMonth()->subDay();  // 15th of next month
            $dueOn  = $end->copy();                          // payment due on the 15th
            $now    = now();

            $row->period_end   = $end->toDateString();
            $row->due_on       = $dueOn->toDateString();
            $row->is_overdue   = $now->isAfter($dueOn) && $row->unpaid_amount > 0;
            $row->is_current   = $now->betweenIncluded($start, $end);
            $row->is_complete  = $row->unpaid_amount <= 0;

            return $row;
        });

        // Badge for current period (might not have commissions yet)
        [$curStart, $curEnd] = self::currentPeriod();
        $currentPeriodStart  = $curStart->toDateString();

        return view('commission.period-index', compact('periods', 'branch', 'currentPeriodStart'));
    }

    /** Detail of one period — breakdown per employee. */
    public function show(string $period)
    {
        $start = Carbon::parse($period)->setDay(16)->startOfDay();
        $end   = $start->copy()->addMonth()->subDay()->endOfDay(); // 15th next month 23:59:59

        $branchId = $this->branch->idOrNull();
        $branch   = $branchId ? Branch::find($branchId) : null;

        $employees = DB::table('commissions as c')
            ->leftJoin('employees as e', 'e.id', '=', 'c.employees_id')
            ->whereDate('c.created_at', '>=', $start->toDateString())
            ->whereDate('c.created_at', '<=', $end->toDateString())
            ->when($branchId, fn($q) => $q->where('e.branch_id', $branchId))
            ->selectRaw("
                e.id   AS employee_id,
                e.name AS employee_name,
                e.job_position,
                SUM(CASE WHEN c.role IN ('marketing','adit_override') AND c.status != 'paid' THEN c.amount ELSE 0 END) AS marketing_unpaid,
                SUM(CASE WHEN c.role IN ('installer','teknisi','kenek') AND c.status != 'paid' THEN c.amount ELSE 0 END) AS installer_unpaid,
                SUM(CASE WHEN c.status != 'paid' THEN c.amount ELSE 0 END)   AS total_unpaid,
                SUM(CASE WHEN c.status  = 'paid' THEN c.amount ELSE 0 END)   AS total_paid,
                SUM(c.amount)                                                 AS grand_total,
                COUNT(CASE WHEN c.status != 'paid' THEN 1 END)               AS unpaid_cnt,
                MAX(c.paid_at)                                                AS last_paid_at
            ")
            ->groupByRaw('e.id, e.name, e.job_position')
            ->orderByRaw('total_unpaid DESC, e.name')
            ->get();

        $totalUnpaid = $employees->sum('total_unpaid');
        $totalPaid   = $employees->sum('total_paid');
        $dueOn       = $end->copy()->startOfDay();
        $isOverdue   = now()->isAfter($dueOn) && $totalUnpaid > 0;

        return view('commission.period-show', compact(
            'employees', 'start', 'end', 'period',
            'totalUnpaid', 'totalPaid', 'branch', 'isOverdue', 'dueOn'
        ));
    }

    /** Pay one employee's unpaid commissions for this period. */
    public function pay(Request $request, string $period)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'notes'       => ['nullable', 'string', 'max:500'],
        ]);

        // Same branch restriction payAll() already applies via its query —
        // pay() takes employee_id directly from the request, so it must be
        // checked explicitly here to stop cross-branch commission payout.
        $branchId = $this->branch->idOrNull();
        if ($branchId !== null) {
            $employeeBranchId = \App\Models\Employee::where('id', $data['employee_id'])->value('branch_id');
            abort_unless((int) $employeeBranchId === (int) $branchId, 403);
        }

        $start = Carbon::parse($period)->setDay(16)->startOfDay();
        $end   = $start->copy()->addMonth()->subDay()->endOfDay();

        $this->payEmployeeInPeriod((int) $data['employee_id'], $start, $end, $period, $data['notes'] ?? null);

        return back()->with('success', 'Komisi berhasil ditandai lunas.');
    }

    /** Pay ALL employees with unpaid commissions in this period. */
    public function payAll(Request $request, string $period)
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $start    = Carbon::parse($period)->setDay(16)->startOfDay();
        $end      = $start->copy()->addMonth()->subDay()->endOfDay();
        $branchId = $this->branch->idOrNull();

        $unpaid = Commission::query()
            ->join('employees as e', 'e.id', '=', 'commissions.employees_id')
            ->where('commissions.status', 'eligible')
            ->where('commissions.is_active', true)
            ->whereNull('commissions.commission_payment_batch_id')
            ->whereDate('commissions.created_at', '>=', $start->toDateString())
            ->whereDate('commissions.created_at', '<=', $end->toDateString())
            ->when($branchId, fn($q) => $q->where('e.branch_id', $branchId))
            ->select('commissions.*')
            ->get();

        if ($unpaid->isEmpty()) {
            return back()->with('error', 'Tidak ada komisi yang belum dibayar di periode ini.');
        }

        foreach ($unpaid->pluck('employees_id')->unique() as $employeeId) {
            $this->payEmployeeInPeriod((int) $employeeId, $start, $end, $period, $request->input('notes'));
        }

        return back()->with('success', 'Semua komisi periode ini berhasil ditandai lunas.');
    }

    /**
     * Bayar semua komisi eligible, AKTIF, dan belum masuk batch milik satu karyawan di periode ini.
     * Dikunci agar klik ganda tidak membayar dua kali; nomor batch dibuat unik per pembayaran;
     * kasbon potong-komisi diterapkan seperti jalur persetujuan pencairan.
     */
    private function payEmployeeInPeriod(int $employeeId, Carbon $start, Carbon $end, string $period, ?string $notes): void
    {
        DB::transaction(function () use ($employeeId, $start, $end, $period, $notes) {
            $commissions = Commission::where('employees_id', $employeeId)
                ->where('status', 'eligible')
                ->where('is_active', true)
                ->whereNull('commission_payment_batch_id')
                ->whereDate('created_at', '>=', $start->toDateString())
                ->whereDate('created_at', '<=', $end->toDateString())
                ->lockForUpdate()
                ->get();

            if ($commissions->isEmpty()) {
                return;
            }

            $now  = now();
            $base = 'KOM-' . Carbon::parse($period)->format('Ym') . '-' . $employeeId;
            $number = $base;
            for ($n = 2; CommissionPaymentBatch::where('batch_number', $number)->exists(); $n++) {
                $number = $base . '-' . $n;
            }

            $batch = CommissionPaymentBatch::create([
                'employee_id'   => $employeeId,
                'batch_number'  => $number,
                'period_from'   => $start->toDateString(),
                'period_to'     => $end->copy()->startOfDay()->toDateString(),
                'payment_date'  => $now->toDateString(),
                'status'        => 'paid',
                'total_amount'  => (int) $commissions->sum('amount'),
                'created_by'    => auth()->id(),
                'paid_by'       => auth()->id(),
                'paid_at'       => $now,
                'notes'         => $notes,
            ]);

            Commission::whereIn('id', $commissions->pluck('id'))->update([
                'commission_payment_batch_id' => $batch->id,
                'status'                      => 'paid',
                'paid_at'                     => $now,
                'paid_by'                     => auth()->id(),
            ]);

            $employee = \App\Models\Employee::find($employeeId);
            if ($employee) {
                app(\App\Services\KasbonDeductionService::class)->applyForCommission($employee, $batch);
            }
        });
    }
}
