<?php

namespace App\Http\Controllers;

use App\Models\EmployeeClaim;
use App\Models\EmployeeHealthBalance;
use App\Models\PettyCashTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeClaimController extends Controller
{
    /** Teknisi/Kenek: daftar klaim milik sendiri. */
    public function index(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun ini tidak terhubung ke data karyawan.');

        $claims = EmployeeClaim::where('employee_id', $employee->id)
            ->latest()
            ->paginate(20);

        return view('my-claims.index', compact('claims'));
    }

    public function create(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun ini tidak terhubung ke data karyawan.');

        $healthBalance = EmployeeHealthBalance::where('employee_id', $employee->id)
            ->where('year', now()->year)
            ->first();

        return view('my-claims.create', compact('healthBalance'));
    }

    public function store(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun ini tidak terhubung ke data karyawan.');

        $data = $request->validate([
            'type'        => ['required', 'in:pembelian,bensin,kesehatan'],
            'amount'      => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:1000'],
            'proof'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($data['type'] === 'kesehatan') {
            $balance = EmployeeHealthBalance::where('employee_id', $employee->id)
                ->where('year', now()->year)
                ->first();

            $remaining = $balance?->remaining() ?? 0;

            if ($data['amount'] > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => 'Nominal melebihi sisa saldo biaya kesehatan tahun ini (Rp ' . number_format($remaining, 0, ',', '.') . ').',
                ]);
            }
        }

        $proofPath = $request->hasFile('proof')
            ? $request->file('proof')->store('employee-claims', 'public')
            : null;

        $claim = DB::transaction(function () use ($data, $employee, $proofPath, $request) {
            $claim = EmployeeClaim::create([
                'employee_id' => $employee->id,
                'branch_id'   => $employee->branch_id,
                'type'        => $data['type'],
                'amount'      => $data['amount'],
                'description' => $data['description'] ?? null,
                'proof_path'  => $proofPath,
                'status'      => 'pending_approval',
            ]);

            $claim->submitForApproval('employee_claim', $request->user()->id);

            return $claim;
        });

        return redirect()->route('my-claims.index')
            ->with('success', ucfirst(EmployeeClaim::$typeLabels[$claim->type]) . ' berhasil diajukan, menunggu approval Manager Operasional.');
    }

    /** Finance: daftar klaim yang sudah disetujui Manager, siap dicairkan. */
    public function readyForDisbursement()
    {
        $claims = EmployeeClaim::with(['employee', 'branch'])
            ->where('status', 'approved')
            ->latest()
            ->paginate(20);

        return view('my-claims.disbursement-queue', compact('claims'));
    }

    /** Finance: cairkan klaim — bikin PettyCashTransaction 'out' otomatis terisi dari data klaim. */
    public function disburse(Request $request, EmployeeClaim $claim)
    {
        abort_unless($claim->status === 'approved', 404, 'Klaim ini belum disetujui atau sudah dicairkan.');

        DB::transaction(function () use ($claim, $request) {
            $locked = EmployeeClaim::whereKey($claim->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'approved') {
                throw ValidationException::withMessages(['claim' => 'Klaim ini sudah diproses di request lain.']);
            }

            $transaction = PettyCashTransaction::create([
                'branch_id'   => $locked->branch_id,
                'type'        => 'out',
                'date'        => now()->toDateString(),
                'employee_id' => $locked->employee_id,
                'item_name'   => EmployeeClaim::$typeLabels[$locked->type] ?? $locked->type,
                'amount'      => $locked->amount,
                'description' => $locked->description ?: EmployeeClaim::$typeLabels[$locked->type],
                'created_by'  => $request->user()->id,
            ]);

            $locked->update([
                'status'                    => 'paid',
                'petty_cash_transaction_id' => $transaction->id,
                'disbursed_by'              => $request->user()->id,
                'disbursed_at'              => now(),
            ]);
        });

        return back()->with('success', 'Klaim berhasil dicairkan lewat Petty Cash.');
    }
}
