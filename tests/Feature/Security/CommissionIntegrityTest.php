<?php

namespace Tests\Feature\Security;

use App\Models\Branch;
use App\Models\Commission;
use App\Models\CommissionPaymentBatch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\SalesNetworkProfile;
use App\Models\SalesProduct;
use App\Models\SalesTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Integritas uang: batas diskon/DP sales, rilis komisi bukan oleh sales, invoice tak bisa dibuat 'paid', total pencairan dihitung ulang (PR-H7). */
class CommissionIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private function salesUser(Branch $branch): array
    {
        Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);
        $emp = Employee::factory()->create(['branch_id' => $branch->id, 'job_position' => 'Sales']);
        SalesNetworkProfile::create(['employee_id' => $emp->id, 'referral_code' => 'REF-ABC123', 'status' => 'active']);
        $user = User::factory()->create(['employee_id' => $emp->id]);
        $user->assignRole('sales');
        $user->branches()->attach($branch->id);

        return [$user, $emp];
    }

    private function product(): SalesProduct
    {
        return SalesProduct::create(['name' => 'Paket', 'category' => 'mobil', 'price' => 1000000, 'is_active' => true]);
    }

    private function transaction(Branch $branch, Employee $emp, string $status = 'paid'): SalesTransaction
    {
        $p = $this->product();

        return SalesTransaction::create([
            'branch_id' => $branch->id, 'sales_employee_id' => $emp->id, 'sales_product_id' => $p->id,
            'customer_name' => 'X', 'price' => 1000000, 'discount' => 0, 'total_price' => 1000000,
            'downpayment' => 1000000, 'remaining_payment' => 0, 'status' => $status, 'paid_at' => now(), 'pelunasan_at' => now(),
        ]);
    }

    public function test_sales_discount_over_limit_is_rejected(): void
    {
        $branch = Branch::factory()->create();
        [$user] = $this->salesUser($branch);
        $p = $this->product();

        $this->actingAs($user)->post(route('sales-transactions.store'), [
            'sales_product_id' => $p->id, 'customer_name' => 'X', 'discount' => 999999,
        ])->assertSessionHasErrors('discount');

        $this->assertDatabaseCount('sales_transactions', 0);
    }

    public function test_sales_cannot_create_an_already_paid_transaction(): void
    {
        $branch = Branch::factory()->create();
        [$user] = $this->salesUser($branch);
        $p = $this->product();

        $this->actingAs($user)->post(route('sales-transactions.store'), [
            'sales_product_id' => $p->id, 'customer_name' => 'X', 'downpayment' => 1000000,
        ])->assertSessionHasErrors('downpayment');

        $this->assertDatabaseCount('sales_transactions', 0);
    }

    public function test_sales_cannot_release_own_commission_or_record_payment(): void
    {
        $branch = Branch::factory()->create();
        [$user, $emp] = $this->salesUser($branch);
        $trx = $this->transaction($branch, $emp);

        $this->actingAs($user)->post(route('sales-transactions.release-commission', $trx->id))->assertForbidden();
        $this->assertNull($trx->fresh()->commission_released_at);

        $unpaid = SalesTransaction::create([
            'branch_id' => $branch->id, 'sales_employee_id' => $emp->id, 'sales_product_id' => $trx->sales_product_id,
            'customer_name' => 'Y', 'price' => 1000000, 'discount' => 0, 'total_price' => 1000000,
            'downpayment' => 0, 'remaining_payment' => 1000000, 'status' => 'unpaid',
        ]);
        $this->actingAs($user)->post(route('sales-transactions.payment', $unpaid->id), ['amount' => 1000000])->assertForbidden();
        $this->assertSame('unpaid', $unpaid->fresh()->status);
    }

    public function test_admin_can_release_commission_once(): void
    {
        $branch = Branch::factory()->create();
        [, $emp] = $this->salesUser($branch);
        $trx = $this->transaction($branch, $emp);
        $admin = $this->userWithRole('admin', $branch);

        $this->actingAs($admin)->post(route('sales-transactions.release-commission', $trx->id))->assertRedirect();
        $this->assertNotNull($trx->fresh()->commission_released_at);

        $this->actingAs($admin)->post(route('sales-transactions.release-commission', $trx->id))->assertStatus(422);
    }

    public function test_invoice_paid_requires_full_downpayment_and_discount_cannot_exceed_price(): void
    {
        $branch = Branch::factory()->create();
        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $admin = $this->userWithRole('admin', $branch);

        $base = [
            'customer_id' => $customer->id, 'price' => 5000000, 'status' => 'unpaid',
            'invoice_date' => now()->toDateString(),
        ];

        $this->actingAs($admin)->post(route('invoice.store'), array_merge($base, ['status' => 'paid']))
            ->assertSessionHasErrors('status');
        $this->actingAs($admin)->post(route('invoice.store'), array_merge($base, ['discount' => 9000000]))
            ->assertSessionHasErrors('discount');
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_withdrawal_approval_recomputes_total_and_skips_deactivated_commissions(): void
    {
        $branch = Branch::factory()->create();
        $emp = Employee::factory()->create(['branch_id' => $branch->id, 'job_position' => 'Teknisi']);
        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id]);
        $approver = $this->userWithRole('finance', $branch);

        $batch = CommissionPaymentBatch::create([
            'employee_id' => $emp->id, 'batch_number' => 'WD-1', 'period_from' => now()->toDateString(),
            'period_to' => now()->toDateString(), 'payment_date' => now()->toDateString(),
            'status' => 'draft', 'total_amount' => 300000, 'created_by' => $approver->id,
        ]);

        $mk = fn (int $amount, bool $active) => DB::table('commissions')->insertGetId([
            'employees_id' => $emp->id, 'customer_id' => $customer->id, 'invoice_id' => $invoice->id,
            'amount' => $amount, 'role' => 'tukang' . $amount, 'status' => 'eligible', 'is_active' => $active,
            'commission_payment_batch_id' => $batch->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $good = $mk(100000, true);
        $dead = $mk(200000, false);

        $this->actingAs($approver)->post(route('commission.withdrawals.approve', $batch->id))->assertRedirect();

        $this->assertSame(100000, (int) $batch->fresh()->total_amount);
        $this->assertSame('paid', Commission::find($good)->status);
        $this->assertSame('eligible', Commission::find($dead)->status);
    }
}
