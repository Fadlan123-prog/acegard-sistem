<?php

namespace App\Http\Controllers;

use App\Models\Kasbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KasbonController extends Controller
{
    public function index(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun ini tidak terhubung ke data karyawan.');

        $kasbons = Kasbon::where('employee_id', $employee->id)
            ->latest()
            ->paginate(20);

        return view('my-kasbon.index', compact('kasbons'));
    }

    public function create(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun ini tidak terhubung ke data karyawan.');

        return view('my-kasbon.create', compact('employee'));
    }

    public function store(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun ini tidak terhubung ke data karyawan.');

        $data = $request->validate([
            'amount'           => ['required', 'integer', 'min:1'],
            'installments'     => ['required', 'integer', 'min:1', 'max:24'],
            'deduction_source' => ['required', 'in:payroll,commission,both'],
            'reason'           => ['required', 'string', 'max:1000'],
        ], [
            'reason.required' => 'Alasan pengajuan kasbon wajib diisi.',
        ]);

        $installmentAmount = (int) ceil($data['amount'] / $data['installments']);
        $monthlySalary = (int) ($employee->monthly_salary ?? 0);
        $cap = (int) floor(0.5 * $monthlySalary);

        if ($monthlySalary > 0 && $installmentAmount > $cap) {
            throw ValidationException::withMessages([
                'installments' => "Potongan per cicilan (Rp " . number_format($installmentAmount, 0, ',', '.')
                    . ") melebihi 50% gaji bulanan Anda (maks. Rp " . number_format($cap, 0, ',', '.')
                    . "). Perbesar jumlah cicilan.",
            ]);
        }

        $kasbon = Kasbon::create([
            'employee_id'         => $employee->id,
            'branch_id'           => $employee->branch_id,
            'amount'              => $data['amount'],
            'reason'              => $data['reason'],
            'installments'        => $data['installments'],
            'installment_amount'  => $installmentAmount,
            'deduction_source'    => $data['deduction_source'],
            'status'              => 'pending_approval',
            'remaining_amount'    => $data['amount'],
            'requested_by'        => $request->user()->id,
        ]);

        $kasbon->submitForApproval('kasbon', $request->user()->id);

        return redirect()->route('my-kasbon.index')
            ->with('success', 'Pengajuan kasbon berhasil dikirim, menunggu approval Manager Operasional.');
    }

    public function show(Request $request, Kasbon $kasbon)
    {
        $user = $request->user();
        $isAdmin = $user->hasAnyRole(['superadmin', 'admin', 'finance', 'it_manager']);

        if ($isAdmin) {
            if (! $user->hasRole('superadmin')) {
                $allowedBranchIds = $user->branches()->pluck('branches.id')->all();
                abort_unless(in_array($kasbon->branch_id, $allowedBranchIds), 403);
            }
        } else {
            $employee = $user->employee;
            abort_unless($employee && $kasbon->employee_id === $employee->id, 403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        $kasbon->load(['employee', 'branch', 'deductions' => fn ($q) => $q->latest()]);

        return view('my-kasbon.show', compact('kasbon'));
    }

    /** Admin/finance: daftar seluruh kasbon lintas karyawan untuk audit — persetujuan tetap lewat /approvals. */
    public function manage(Request $request)
    {
        $status = $request->get('status', '');

        $kasbons = Kasbon::with(['employee', 'branch'])
            ->accessibleByUser()
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('kasbons.index', [
            'kasbons' => $kasbons,
            'filters' => ['status' => $status],
        ]);
    }
}
