<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Payable;
use App\Services\ExpenseSummaryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Total pengeluaran Analytics: petty cash + gaji dibayar + komisi dibayar + hutang dibayar; PO terpisah (memo). */
class ExpenseSummaryTest extends TestCase
{
    use RefreshDatabase;

    private Carbon $start;
    private Carbon $end;

    protected function setUp(): void
    {
        parent::setUp();
        $this->start = Carbon::parse('2026-10-01')->startOfDay();
        $this->end = Carbon::parse('2026-10-31')->endOfDay();
    }

    private function petty(Branch $b, string $type, int $amount, string $date = '2026-10-10'): void
    {
        $emp = Employee::factory()->create(['branch_id' => $b->id]);
        DB::table('petty_cash_transactions')->insert([
            'branch_id' => $b->id, 'type' => $type, 'date' => $date, 'employee_id' => $emp->id, 'item_name' => 'x',
            'amount' => $amount, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function payroll(Branch $b, string $month, string $status, int $salary): void
    {
        $id = DB::table('payrolls')->insertGetId(['branch_id' => $b->id, 'month' => $month, 'status' => $status, 'created_at' => now(), 'updated_at' => now()]);
        $emp = Employee::factory()->create(['branch_id' => $b->id]);
        DB::table('payroll_items')->insert(['payroll_id' => $id, 'employee_id' => $emp->id, 'employee_name' => 'X', 'rounded_salary' => $salary, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function commission(Employee $e, int $amount, string $status, ?string $paidAt, ?int $branchId): void
    {
        DB::table('commissions')->insert([
            'employees_id' => $e->id, 'amount' => $amount, 'status' => $status, 'paid_at' => $paidAt, 'branch_id' => $branchId,
            'role' => 'r' . uniqid(), 'is_active' => true, 'created_at' => '2026-09-01 10:00:00', 'updated_at' => now(),
        ]);
    }

    public function test_each_category_is_summed_and_the_total_is_the_sum_of_cash_categories(): void
    {
        $b = Branch::factory()->create();
        $emp = Employee::factory()->create(['branch_id' => $b->id]);
        $user = $this->userWithRole('admin', $b);

        $this->petty($b, 'out', 100000);
        $this->petty($b, 'purchase', 50000);
        $this->petty($b, 'in', 999999);                       // kas masuk bukan pengeluaran
        $this->payroll($b, '2026-10', 'paid', 5000000);
        $this->payroll($b, '2026-11', 'draft', 7000000);      // dalam rentang tapi belum dibayar
        $this->payroll($b, '2026-09', 'paid', 4000000);       // bulan di luar rentang
        $this->commission($emp, 300000, 'paid', '2026-10-15 09:00:00', $b->id);
        $this->commission($emp, 200000, 'eligible', null, $b->id);   // belum dibayar
        $payable = Payable::create(['branch_id' => $b->id, 'vendor_name' => 'V', 'description' => 'd', 'amount' => 1000000, 'created_by' => $user->id]);
        DB::table('debt_payments')->insert(['payable_type' => Payable::class, 'payable_id' => $payable->id, 'amount' => 400000, 'payment_date' => '2026-10-20',
            'status' => 'approved', 'recorded_by' => $user->id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('debt_payments')->insert(['payable_type' => Payable::class, 'payable_id' => $payable->id, 'amount' => 111111, 'payment_date' => '2026-10-21',
            'status' => 'pending_approval', 'recorded_by' => $user->id, 'created_at' => now(), 'updated_at' => now()]);   // belum disetujui
        DB::table('purchases')->insert(['branch_id' => $b->id, 'purchase_number' => 'PO-1', 'purchase_date' => '2026-10-12', 'total_amount' => 9000000, 'status' => 'confirmed', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('purchases')->insert(['branch_id' => $b->id, 'purchase_number' => 'PO-2', 'purchase_date' => '2026-10-12', 'total_amount' => 3000000, 'status' => 'draft', 'created_at' => now(), 'updated_at' => now()]);

        $r = app(ExpenseSummaryService::class)->summarize($this->start, Carbon::parse('2026-11-30')->endOfDay(), $b->id);
        $by = collect($r['rows'])->pluck('amount', 'key');

        $this->assertSame(100000, $by['petty_out']);
        $this->assertSame(50000, $by['petty_purchase']);
        $this->assertSame(5000000, $by['salary']);
        $this->assertSame(300000, $by['commission']);
        $this->assertSame(400000, $by['debt']);
        $this->assertSame(5850000, $r['total']);
        $this->assertSame(9000000, $r['po_commitment'], 'PO draft tidak dihitung');
        $this->assertSame(14850000, $r['total_with_po']);
    }

    public function test_commission_uses_the_paid_date_and_falls_back_to_the_employee_branch(): void
    {
        $b = Branch::factory()->create();
        $other = Branch::factory()->create();
        $emp = Employee::factory()->create(['branch_id' => $b->id]);
        $foreign = Employee::factory()->create(['branch_id' => $other->id]);

        $this->commission($emp, 100000, 'paid', '2026-10-05 10:00:00', null);       // komisi lama tanpa branch_id -> cabang karyawan
        $this->commission($emp, 70000, 'paid', '2026-11-02 10:00:00', $b->id);       // dibayar bulan depan: tidak masuk Oktober
        $this->commission($foreign, 500000, 'paid', '2026-10-05 10:00:00', $other->id);

        $r = app(ExpenseSummaryService::class)->summarize($this->start, $this->end, $b->id);
        $this->assertSame(100000, collect($r['rows'])->firstWhere('key', 'commission')['amount']);

        $all = app(ExpenseSummaryService::class)->summarize($this->start, $this->end, null);
        $this->assertSame(600000, collect($all['rows'])->firstWhere('key', 'commission')['amount'], 'tanpa filter cabang = semua cabang');
    }

    public function test_branch_filter_excludes_other_branches_in_every_category(): void
    {
        $mine = Branch::factory()->create();
        $other = Branch::factory()->create();
        $this->petty($other, 'out', 777000);
        $this->payroll($other, '2026-10', 'paid', 6000000);

        $r = app(ExpenseSummaryService::class)->summarize($this->start, $this->end, $mine->id);

        $this->assertSame(0, $r['total']);
    }

    public function test_analytics_page_shows_the_breakdown_and_the_memo_for_po(): void
    {
        $b = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $b);
        $this->petty($b, 'out', 100000, now()->toDateString());

        $this->actingAs($admin)->get(route('analytics.index'))
            ->assertOk()->assertSee('Rincian Total Pengeluaran')->assertSee('Gaji karyawan')->assertSee('Komisi dibayarkan')
            ->assertSee('Pembayaran hutang vendor')->assertSee('Memo: pembelian PO')->assertSee('Total Pengeluaran');
    }
}
