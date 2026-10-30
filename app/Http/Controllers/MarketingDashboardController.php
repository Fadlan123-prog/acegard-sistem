<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\CommissionPaymentBatch;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketingDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $userId = auth()->id();
        $month  = now()->month;
        $year   = now()->year;

        // My leads
        $myLeadsTotal  = Lead::where('created_by', $userId)->count();
        $myLeadsActive = Lead::where('created_by', $userId)
            ->whereNotIn('status', ['won', 'lost'])
            ->count();
        $myLeadsWon    = Lead::where('created_by', $userId)
            ->where('status', 'won')
            ->whereMonth('converted_at', $month)
            ->whereYear('converted_at', $year)
            ->count();

        $myLeadsThisMonth = Lead::where('created_by', $userId)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count();

        // Conversion rate this month
        $conversionRate = $myLeadsThisMonth > 0
            ? round(($myLeadsWon / $myLeadsThisMonth) * 100, 1)
            : 0;

        // My commission this month
        $myEmployee = auth()->user()->employee ?? null;
        $myCommissionMonth = 0;
        $myCommissionTotal = 0;
        if ($myEmployee) {
            $myCommissionMonth = (float) DB::table('commissions')
                ->where('employees_id', $myEmployee->id)
                ->where('role', 'marketing')
                ->whereIn('status', ['eligible', 'paid'])
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->sum('amount');

            $myCommissionTotal = (float) DB::table('commissions')
                ->where('employees_id', $myEmployee->id)
                ->where('role', 'marketing')
                ->where('status', 'eligible')
                ->sum('amount');
        }

        // Leads needing follow-up (follow_up_date <= today or status = new/contacted)
        $followUpLeads = Lead::where('created_by', $userId)
            ->where(function ($q) {
                $q->whereIn('status', ['new', 'contacted', 'follow_up'])
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('follow_up_date')
                         ->where('follow_up_date', '<=', today());
                  });
            })
            ->orderBy('follow_up_date')
            ->limit(15)
            ->get();

        // Recent leads
        $recentLeads = Lead::where('created_by', $userId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Lead funnel (my leads)
        $leadFunnel = Lead::where('created_by', $userId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Latest withdrawal batch for display on dashboard
        $latestWithdrawal = null;
        $canRequestWithdrawal = false;
        $nowWib = Carbon::now('Asia/Jakarta');

        if ($myEmployee) {
            $latestWithdrawal = CommissionPaymentBatch::where('employee_id', $myEmployee->id)
                ->latest()
                ->first();

            $eligibleUnbatched = Commission::where('employees_id', $myEmployee->id)
                ->where('role', 'marketing')
                ->where('status', 'eligible')
                ->whereNull('commission_payment_batch_id')
                ->exists();

            $pendingExists = CommissionPaymentBatch::where('employee_id', $myEmployee->id)
                ->where('status', 'draft')
                ->whereYear('payment_date', $nowWib->year)
                ->whereMonth('payment_date', $nowWib->month)
                ->exists();

            $canRequestWithdrawal = $nowWib->day === 15 && $eligibleUnbatched && !$pendingExists;
        }

        $upcomingBirthdays = \App\Models\Employee::upcomingBirthdays($myEmployee?->branch_id);

        return view('marketing.dashboard', compact(
            'myLeadsTotal', 'myLeadsActive', 'myLeadsWon',
            'myLeadsThisMonth', 'conversionRate',
            'myCommissionMonth', 'myCommissionTotal',
            'followUpLeads', 'recentLeads', 'leadFunnel',
            'latestWithdrawal', 'canRequestWithdrawal', 'nowWib',
            'upcomingBirthdays',
        ));
    }

    public function komisSaya(Request $request)
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            abort(403, 'Akun Anda belum terhubung ke data karyawan. Hubungi superadmin.');
        }

        $commissions = Commission::query()
            ->with([
                'customer:id,name,vehicle_brand,vehicle_model,plat_number',
                'invoice:id,invoice_number,status,total_price',
                'batch:id,status,batch_number,paid_at',
            ])
            ->where('employees_id', $employee->id)
            ->where('role', 'marketing')
            ->whereNotIn('status', ['cancelled'])
            ->latest()
            ->paginate(20);

        $summary = [
            'pending_total'  => Commission::where('employees_id', $employee->id)->where('role', 'marketing')->where('status', 'pending')->sum('amount'),
            'eligible_total' => Commission::where('employees_id', $employee->id)->where('role', 'marketing')->where('status', 'eligible')->sum('amount'),
            'paid_total'     => Commission::where('employees_id', $employee->id)->where('role', 'marketing')->where('status', 'paid')->sum('amount'),
        ];

        $nowWib = Carbon::now('Asia/Jakarta');
        $eligibleUnbatched = Commission::where('employees_id', $employee->id)
            ->where('role', 'marketing')
            ->where('status', 'eligible')
            ->where('is_active', true)
            ->whereNull('commission_payment_batch_id')
            ->get();

        $pendingWithdrawal = CommissionPaymentBatch::where('employee_id', $employee->id)
            ->where('status', 'draft')
            ->whereYear('payment_date', $nowWib->year)
            ->whereMonth('payment_date', $nowWib->month)
            ->first();

        $canRequestWithdrawal = $nowWib->day === 15
            && $eligibleUnbatched->isNotEmpty()
            && $pendingWithdrawal === null;

        return view('marketing.komisi', [
            'title'                  => 'Komisi Saya',
            'employee'               => $employee,
            'commissions'            => $commissions,
            'summary'                => $summary,
            'canRequestWithdrawal'   => $canRequestWithdrawal,
            'eligibleUnbatchedTotal' => $eligibleUnbatched->sum('amount'),
            'eligibleUnbatchedCount' => $eligibleUnbatched->count(),
            'pendingWithdrawal'      => $pendingWithdrawal,
            'nowWib'                 => $nowWib,
        ]);
    }

    public function requestWithdrawal(Request $request)
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            abort(403, 'Akun tidak terhubung ke data karyawan.');
        }

        $nowWib = Carbon::now('Asia/Jakarta');

        if ($nowWib->day !== 15) {
            return back()->with('error', 'Pengajuan pencairan hanya dapat dilakukan pada tanggal 15 setiap bulan (WIB).');
        }

        $alreadyPending = CommissionPaymentBatch::where('employee_id', $employee->id)
            ->where('status', 'draft')
            ->whereYear('payment_date', $nowWib->year)
            ->whereMonth('payment_date', $nowWib->month)
            ->exists();

        if ($alreadyPending) {
            return back()->with('error', 'Anda sudah memiliki pengajuan pencairan yang sedang menunggu persetujuan bulan ini.');
        }

        $eligibleCommissions = Commission::where('employees_id', $employee->id)
            ->where('role', 'marketing')
            ->where('status', 'eligible')
            ->whereNull('commission_payment_batch_id')
            ->get();

        if ($eligibleCommissions->isEmpty()) {
            return back()->with('error', 'Tidak ada komisi yang siap dicairkan saat ini.');
        }

        $request->validate(['requester_notes' => ['nullable', 'string', 'max:500']]);

        $total = $eligibleCommissions->sum('amount');

        DB::transaction(function () use ($employee, &$eligibleCommissions, &$total, $nowWib, $request) {
            $eligibleCommissions = Commission::where('employees_id', $employee->id)
                ->where('role', 'marketing')
                ->where('status', 'eligible')
                ->where('is_active', true)
                ->whereNull('commission_payment_batch_id')
                ->lockForUpdate()
                ->get();
            $total = $eligibleCommissions->sum('amount');

            if ($eligibleCommissions->isEmpty()) {
                return;
            }

            $batchNumber = 'WD-' . $nowWib->format('Ym') . '-' . strtoupper(substr($employee->name, 0, 3)) . '-' . $employee->id;

            $batch = CommissionPaymentBatch::create([
                'employee_id'     => $employee->id,
                'batch_number'    => $batchNumber,
                'period_from'     => $nowWib->copy()->startOfMonth()->toDateString(),
                'period_to'       => $nowWib->toDateString(),
                'payment_date'    => $nowWib->toDateString(),
                'status'          => 'draft',
                'total_amount'    => $total,
                'created_by'      => auth()->id(),
                'requester_notes' => $request->input('requester_notes'),
            ]);

            Commission::whereIn('id', $eligibleCommissions->pluck('id'))
                ->update(['commission_payment_batch_id' => $batch->id]);
        });

        return back()->with('success', 'Pengajuan pencairan komisi Rp ' . number_format($total) . ' berhasil dikirim. Menunggu persetujuan superadmin.');
    }
}
