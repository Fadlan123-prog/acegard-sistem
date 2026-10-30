<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\CustomerPpf;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\PpfInvoice;
use App\Models\User;
use App\Services\CommissionEligibilityService;
use App\Services\CommissionReleaseService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Perbaikan audit komisi K1-K13: uang tidak boleh dibayar ganda / nonaktif / dipindah setelah dibayar. */
class CommissionIntegrityFixesTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;
    private Employee $emp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branch = Branch::factory()->create();
        $this->emp = Employee::factory()->create(['branch_id' => $this->branch->id, 'job_position' => 'Teknisi']);
    }

    private function commission(array $over = []): int
    {
        $customer = Customer::factory()->create(['branch_id' => $this->branch->id]);
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id]);

        return DB::table('commissions')->insertGetId(array_merge([
            'employees_id' => $this->emp->id, 'customer_id' => $customer->id, 'invoice_id' => $invoice->id,
            'branch_id' => $this->branch->id, 'amount' => 100000, 'role' => 'installer' . uniqid(),
            'status' => 'eligible', 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ], $over));
    }

    private function commStatus(int $id): ?string
    {
        return DB::table('commissions')->where('id', $id)->value('status');
    }

    // K1 ─────────────────────────────────────────────────────────────
    public function test_period_payment_skips_inactive_and_already_batched_commissions(): void
    {
        $admin = $this->userWithRole('superadmin', $this->branch);
        $good = $this->commission();
        $inactive = $this->commission(['is_active' => false]);
        $batchId = DB::table('commission_payment_batches')->insertGetId([
            'employee_id' => $this->emp->id, 'batch_number' => 'WD-X', 'period_from' => now()->toDateString(),
            'period_to' => now()->toDateString(), 'payment_date' => now()->toDateString(), 'status' => 'draft',
            'total_amount' => 100000, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $requested = $this->commission(['commission_payment_batch_id' => $batchId]);

        $period = Carbon::now()->startOfMonth()->setDay(16)->toDateString();
        if (now()->day < 16) {
            $period = now()->subMonth()->startOfMonth()->setDay(16)->toDateString();
        }
        DB::table('commissions')->update(['created_at' => Carbon::parse($period)->addDay()]);

        $this->actingAs($admin)->post(route('commission.periods.pay-all', $period))->assertRedirect();

        $this->assertSame('paid', $this->commStatus($good));
        $this->assertSame('eligible', $this->commStatus($inactive), 'komisi nonaktif tidak dibayar');
        $this->assertSame('eligible', $this->commStatus($requested), 'komisi yang sedang diajukan tidak dibayar ganda');

        // pembayaran kedua pada periode & karyawan yang sama tidak gagal oleh nomor batch yang sama
        $late = $this->commission(['created_at' => Carbon::parse($period)->addDays(2)]);
        $this->actingAs($admin)->post(route('commission.periods.pay-all', $period))->assertRedirect();
        $this->assertSame('paid', $this->commStatus($late));
        $this->assertSame(2, DB::table('commission_payment_batches')->where('batch_number', 'like', 'KOM-%')->count());
    }

    // K2 ─────────────────────────────────────────────────────────────
    public function test_withdrawal_request_excludes_inactive_commissions(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-15 10:00:00', 'Asia/Jakarta'));
        $user = $this->userWithRole('teknisi', $this->branch);
        $user->update(['employee_id' => $this->emp->id]);
        $good = $this->commission();
        $inactive = $this->commission(['is_active' => false]);

        $this->actingAs($user)->post(route('teknisi.withdrawal.request'))->assertRedirect();

        $batch = DB::table('commission_payment_batches')->where('employee_id', $this->emp->id)->first();
        $this->assertNotNull($batch);
        $this->assertSame(100000, (int) $batch->total_amount);
        $this->assertNotNull(DB::table('commissions')->where('id', $good)->value('commission_payment_batch_id'));
        $this->assertNull(DB::table('commissions')->where('id', $inactive)->value('commission_payment_batch_id'));
        Carbon::setTestNow();
    }

    // K3 + K4 ────────────────────────────────────────────────────────
    private function ppfInvoice(string $status): PpfInvoice
    {
        $customer = CustomerPpf::factory()->create(['branch_id' => $this->branch->id]);
        $productId = DB::table('ppf_products')->insertGetId([
            'name' => 'P', 'brand' => 'B', 'thickness_microns' => 150, 'finish' => 'gloss', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $installation = \App\Models\CustomerPpfInstallation::create([
            'customer_ppf_id' => $customer->id, 'ppf_product_id' => $productId, 'branch_id' => $this->branch->id,
            'install_type' => 'full_body', 'install_date' => now()->toDateString(), 'warranty_code' => 'W-' . uniqid(),
            'warranty_start' => now()->toDateString(), 'warranty_years' => 5, 'warranty_end' => now()->addYears(5)->toDateString(),
        ]);

        return PpfInvoice::create([
            'branch_id' => $this->branch->id, 'customer_ppf_id' => $customer->id, 'customer_ppf_installation_id' => $installation->id,
            'invoice_number' => 'PPF-' . uniqid(), 'invoice_date' => now()->toDateString(), 'payment_method' => 'cash',
            'price' => 5000000, 'discount' => 0, 'downpayment' => 0, 'total_price' => 5000000,
            'remaining_payment' => 5000000, 'status' => $status,
        ]);
    }

    public function test_ppf_commission_cannot_be_released_before_payment_and_only_once(): void
    {
        $admin = $this->userWithRole('admin', $this->branch);
        $unpaid = $this->ppfInvoice('unpaid');

        $this->actingAs($admin)->post(route('ppf-invoices.release-commission', $unpaid->id))->assertSessionHas('error');
        $this->assertNull($unpaid->fresh()->commission_released_at);

        $paid = $this->ppfInvoice('paid');
        $this->actingAs($admin)->post(route('ppf-invoices.release-commission', $paid->id))->assertSessionHas('success');
        $this->assertNotNull($paid->fresh()->commission_released_at);
        $this->actingAs($admin)->post(route('ppf-invoices.release-commission', $paid->id))->assertSessionHas('error');
    }

    public function test_ppf_admin_commission_is_only_for_the_invoice_branch(): void
    {
        $other = Branch::factory()->create();
        $mine = Employee::factory()->create(['branch_id' => $this->branch->id, 'ppf_admin_commission' => true]);
        $foreign = Employee::factory()->create(['branch_id' => $other->id, 'ppf_admin_commission' => true]);
        $invoice = $this->ppfInvoice('paid');

        $this->actingAs($this->userWithRole('admin', $this->branch))
            ->post(route('ppf-invoices.release-commission', $invoice->id))->assertSessionHas('success');

        $ids = DB::table('commissions')->where('ppf_invoice_id', $invoice->id)->where('role', 'admin')->pluck('employees_id')->all();
        $this->assertSame([$mine->id], $ids);
        $this->assertNotContains($foreign->id, $ids);
    }

    public function test_ppf_invoice_with_paid_commission_cannot_be_deleted_but_unpaid_ones_are_removed(): void
    {
        $admin = $this->userWithRole('admin', $this->branch);

        $a = $this->ppfInvoice('paid');
        DB::table('commissions')->insert(['employees_id' => $this->emp->id, 'ppf_invoice_id' => $a->id, 'branch_id' => $this->branch->id,
            'amount' => 1, 'role' => 'teknisi', 'status' => 'paid', 'created_at' => now(), 'updated_at' => now()]);
        $this->actingAs($admin)->delete(route('ppf-invoices.destroy', $a->id))->assertSessionHas('error');
        $this->assertNotNull(PpfInvoice::find($a->id));

        $b = $this->ppfInvoice('unpaid');
        DB::table('commissions')->insert(['employees_id' => $this->emp->id, 'ppf_invoice_id' => $b->id, 'branch_id' => $this->branch->id,
            'amount' => 1, 'role' => 'teknisi', 'status' => 'eligible', 'created_at' => now(), 'updated_at' => now()]);
        $this->actingAs($admin)->delete(route('ppf-invoices.destroy', $b->id))->assertRedirect();
        $this->assertNull(PpfInvoice::find($b->id));
        $this->assertSame(0, DB::table('commissions')->where('ppf_invoice_id', $b->id)->count());
    }

    // K5 ─────────────────────────────────────────────────────────────
    public function test_paid_or_batched_commission_cannot_be_moved_and_target_must_be_in_branch(): void
    {
        $admin = $this->userWithRole('admin', $this->branch);
        $other = Employee::factory()->create(['branch_id' => $this->branch->id]);
        $foreign = Employee::factory()->create(['branch_id' => Branch::factory()->create()->id]);

        $paid = $this->commission(['status' => 'paid']);
        $this->actingAs($admin)->put(route('commission.move.update', $paid), ['employees_id' => $other->id])->assertSessionHas('error');
        $this->assertSame($this->emp->id, (int) DB::table('commissions')->where('id', $paid)->value('employees_id'));

        $open = $this->commission();
        $this->actingAs($admin)->put(route('commission.move.update', $open), ['employees_id' => $foreign->id])->assertForbidden();
        $this->actingAs($admin)->put(route('commission.move.update', $open), ['employees_id' => $other->id])->assertRedirect();
        $this->assertSame($other->id, (int) DB::table('commissions')->where('id', $open)->value('employees_id'));
    }

    // K6 ─────────────────────────────────────────────────────────────
    public function test_eligibility_sync_does_not_downgrade_requested_commissions(): void
    {
        $customer = Customer::factory()->create(['branch_id' => $this->branch->id]);
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id, 'status' => 'unpaid']);
        $batchId = DB::table('commission_payment_batches')->insertGetId([
            'employee_id' => $this->emp->id, 'batch_number' => 'WD-Y', 'period_from' => now()->toDateString(),
            'period_to' => now()->toDateString(), 'payment_date' => now()->toDateString(), 'status' => 'draft',
            'total_amount' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $batched = DB::table('commissions')->insertGetId(['employees_id' => $this->emp->id, 'invoice_id' => $invoice->id,
            'amount' => 1, 'role' => 'a', 'status' => 'eligible', 'commission_payment_batch_id' => $batchId, 'created_at' => now(), 'updated_at' => now()]);
        $free = DB::table('commissions')->insertGetId(['employees_id' => $this->emp->id, 'invoice_id' => $invoice->id,
            'amount' => 1, 'role' => 'b', 'status' => 'eligible', 'created_at' => now(), 'updated_at' => now()]);

        app(CommissionEligibilityService::class)->syncByInvoice($invoice);

        $this->assertSame('eligible', $this->commStatus($batched));
        $this->assertSame('pending', $this->commStatus($free));
    }

    // K7 ─────────────────────────────────────────────────────────────
    public function test_lebaran_rate_is_restricted_to_superadmin_and_owner(): void
    {
        $customer = Customer::factory()->create(['branch_id' => $this->branch->id, 'tukang_id' => $this->emp->id, 'install_type' => 'fullset']);
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id, 'status' => 'paid', 'pelunasan_at' => now()]);
        $invoice->update(['work_status' => 'completed', 'qc_status' => null]);

        $this->actingAs($this->userWithRole('admin', $this->branch))
            ->post(route('invoice.release-commission', $invoice->id), ['lebaran' => 1])->assertForbidden();
        $this->assertNull($invoice->fresh()->commission_released_at);

        $this->actingAs($this->userWithRole('owner', $this->branch))
            ->post(route('invoice.release-commission', $invoice->id), ['lebaran' => 1])->assertSessionHas('success');
    }

    // K10 + K13 ──────────────────────────────────────────────────────
    public function test_release_keeps_paid_legacy_commissions_and_uses_the_discounted_price(): void
    {
        $this->emp->update(['commission_mode' => 'percentage', 'commission_percentage' => 0.02]);
        $customer = Customer::factory()->create(['branch_id' => $this->branch->id, 'tukang_id' => $this->emp->id, 'install_type' => 'dsp']);
        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id, 'price' => 1500000, 'discount' => 500000, 'total_price' => 1000000, 'remaining_payment' => 1000000,
        ]);
        $legacyPaid = DB::table('commissions')->insertGetId(['employees_id' => $this->emp->id, 'customer_id' => $customer->id,
            'amount' => 5, 'role' => 'installer', 'status' => 'paid', 'created_at' => now(), 'updated_at' => now()]);

        app(CommissionReleaseService::class)->release($invoice, User::factory()->create()->id);

        $this->assertNotNull(DB::table('commissions')->where('id', $legacyPaid)->first(), 'komisi lama yang sudah dibayar tidak dihapus');
        $this->assertSame(20000, (int) DB::table('commissions')->where('invoice_id', $invoice->id)->where('employees_id', $this->emp->id)->value('amount'),
            '2% dari harga setelah diskon (1.000.000), bukan 1.500.000');
    }

    // K11 + K12 ──────────────────────────────────────────────────────
    public function test_slip_is_limited_to_own_branch_and_paid_commission_cannot_be_deactivated(): void
    {
        $admin = $this->userWithRole('admin', $this->branch);
        $foreign = Employee::factory()->create(['branch_id' => Branch::factory()->create()->id]);

        $this->actingAs($admin)->get(route('commission.print', ['employee_id' => $foreign->id, 'from' => '2026-01-01', 'to' => '2026-12-31']))->assertForbidden();
        $this->actingAs($admin)->get(route('commission.print', ['employee_id' => $this->emp->id, 'from' => '2026-01-01', 'to' => '2026-12-31']))->assertOk();

        $paid = $this->commission(['status' => 'paid', 'role' => 'marketing']);
        $this->actingAs($admin)->patch(route('commission.toggle-active', $paid))->assertSessionHas('error');
        $this->assertSame(1, (int) DB::table('commissions')->where('id', $paid)->value('is_active'));
    }
}
