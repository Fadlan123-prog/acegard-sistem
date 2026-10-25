<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression test for the payroll slip PDF deduction breakdown: the "Total Potongan"
 * row used to be the only deduction line shown, collapsing 6 distinct components
 * (potongan rokok, kasbon, potongan absen, keterlambatan, BPJS, lainnya) into one
 * number. Both slip-gaji (single) and slip-gaji-all (all employees) now itemize
 * each component before the total.
 */
class PayrollSlipPdfTest extends TestCase
{
    use RefreshDatabase;

    private function makePayrollWithItem(): array
    {
        $branch = Branch::factory()->create();
        $staff  = $this->userWithRole('admin', $branch);

        $payroll = Payroll::create([
            'branch_id'    => $branch->id,
            'month'        => now()->format('Y-m'),
            'working_days' => 26,
            'status'       => 'draft',
        ]);

        $employee = Employee::factory()->create(['branch_id' => $branch->id]);

        $item = PayrollItem::create([
            'payroll_id'    => $payroll->id,
            'employee_id'   => $employee->id,
            'employee_name' => $employee->name,
            'job_position'  => 'Teknisi',

            'monthly_salary' => 5000000,
            'daily_prorate'  => 227272,

            'present_days'    => 24,
            'off_days'        => 2,
            'off_quota'       => 4,
            'paid_off_days'   => 2,
            'excess_off_days' => 0,
            'sick_days'       => 0,
            'permission_days' => 0,
            'absent_days'     => 1,

            'paid_days'      => 24,
            'prorate_salary' => 4500000,

            'transport_allowance' => 300000,
            'meal_allowance'      => 300000,

            // Every deduction component non-zero so each itemized row renders.
            'main_deduction'       => 100000, // potongan rokok
            'cash_advance'         => 250000, // kasbon
            'attendance_deduction' => 227272, // potongan absen
            'other_deduction'      => 50000,

            'overtime_minutes' => 120,
            'overtime_pay'     => 150000,
            'late_deduction'   => 25000,

            'bpjs_jht_employee'   => 100000,
            'bpjs_jp_employee'    => 50000,
            'bpjs_kes_employee'   => 50000,
            'bpjs_total_employee' => 200000,

            'total_salary'   => 4123728,
            'rounded_salary' => 4124000,
        ]);

        return [$staff, $payroll, $item];
    }

    public function test_single_slip_pdf_itemizes_every_deduction_component(): void
    {
        [$staff, $payroll, $item] = $this->makePayrollWithItem();

        $html = view('payrolls.slip-gaji', [
            'payroll' => $payroll,
            'item'    => $item->load('employee'),
            'branch'  => $payroll->branch,
            'period'  => 'Juli 2026',
        ])->render();

        foreach (['Potongan Rokok', 'Kasbon', 'Potongan Absen', 'Potongan Keterlambatan', 'BPJS (Karyawan)', 'Potongan Lainnya', 'Total Potongan'] as $label) {
            $this->assertStringContainsString($label, $html, "Missing deduction line: {$label}");
        }

        $response = $this->actingAs($staff)
            ->get(route('payrolls.slip', [$payroll->id, $item->id]));

        $response->assertSuccessful();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_all_employees_slip_pdf_itemizes_every_deduction_component(): void
    {
        [$staff, $payroll, $item] = $this->makePayrollWithItem();

        $html = view('payrolls.slip-gaji-all', [
            'payroll' => $payroll->load('items.employee', 'branch'),
            'branch'  => $payroll->branch,
            'period'  => 'Juli 2026',
        ])->render();

        foreach (['Potongan Rokok', 'Kasbon', 'Potongan Absen', 'Potongan Keterlambatan', 'BPJS (Karyawan)', 'Potongan Lainnya', 'Total Potongan'] as $label) {
            $this->assertStringContainsString($label, $html, "Missing deduction line: {$label}");
        }

        $response = $this->actingAs($staff)
            ->get(route('payrolls.slip.all', $payroll->id));

        $response->assertSuccessful();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
