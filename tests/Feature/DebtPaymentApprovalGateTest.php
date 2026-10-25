<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\DebtPayment;
use App\Models\Payable;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Wires the generic approval system (Phase 1) into the real, pre-existing
 * HutangPiutangController::recordPayment(): recording a payment against a
 * Payable (hutang) or Receivable (piutang) now requires Owner approval before
 * it actually reduces the outstanding balance (see DebtPayment::onApprovalDecision()).
 */
class DebtPaymentApprovalGateTest extends TestCase
{
    use RefreshDatabase;

    private function makeFinanceUser(Branch $branch): User
    {
        return $this->userWithRole('finance', $branch);
    }

    public function test_recording_a_hutang_payment_does_not_reduce_balance_until_owner_approves(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);

        $branch = Branch::factory()->create();
        $finance = $this->makeFinanceUser($branch);
        $owner = $this->userWithRole('owner');

        $payable = Payable::create([
            'branch_id' => $branch->id, 'vendor_name' => 'PT Kain Nusantara',
            'description' => 'Pembelian bahan baku', 'amount' => 1000000,
            'paid_amount' => 0, 'status' => 'unpaid', 'created_by' => $finance->id,
        ]);

        $response = $this->actingAs($finance)->post(route('hutang.payment', $payable->id), [
            'amount' => 400000, 'payment_date' => now()->toDateString(), 'payment_method' => 'transfer',
        ]);
        $response->assertRedirect(route('hutang.show', $payable->id));

        $payable->refresh();
        $this->assertSame(0, $payable->paid_amount);
        $this->assertSame('unpaid', $payable->status);

        $payment = DebtPayment::first();
        $this->assertSame('pending_approval', $payment->status);
        $this->assertNotNull($payment->approvalRequest);

        $this->actingAs($owner)->post(route('approvals.approve', $payment->approvalRequest))->assertRedirect();

        $payable->refresh();
        $payment->refresh();
        $this->assertSame(400000, $payable->paid_amount);
        $this->assertSame('partial', $payable->status);
        $this->assertSame('approved', $payment->status);
    }

    public function test_owner_rejecting_a_piutang_payment_leaves_balance_untouched(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);

        $branch = Branch::factory()->create();
        $finance = $this->makeFinanceUser($branch);
        $owner = $this->userWithRole('owner');

        $receivable = Receivable::create([
            'branch_id' => $branch->id, 'customer_name' => 'Budi Santoso',
            'description' => 'Jasa PPF mobil', 'amount' => 2000000,
            'paid_amount' => 0, 'status' => 'unpaid', 'created_by' => $finance->id,
        ]);

        $this->actingAs($finance)->post(route('piutang.payment', $receivable->id), [
            'amount' => 500000, 'payment_date' => now()->toDateString(), 'payment_method' => 'cash',
        ])->assertRedirect(route('piutang.show', $receivable->id));

        $payment = DebtPayment::first();

        $this->actingAs($owner)
            ->post(route('approvals.reject', $payment->approvalRequest), ['note' => 'Belum ada bukti transfer.'])
            ->assertRedirect();

        $receivable->refresh();
        $payment->refresh();
        $this->assertSame(0, $receivable->paid_amount);
        $this->assertSame('unpaid', $receivable->status);
        $this->assertSame('rejected', $payment->status);
    }

    public function test_pending_payment_cannot_be_deleted_directly(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);

        $branch = Branch::factory()->create();
        $finance = $this->makeFinanceUser($branch);

        $payable = Payable::create([
            'branch_id' => $branch->id, 'vendor_name' => 'PT Kain Nusantara',
            'description' => 'Pembelian bahan baku', 'amount' => 1000000,
            'paid_amount' => 0, 'status' => 'unpaid', 'created_by' => $finance->id,
        ]);

        $this->actingAs($finance)->post(route('hutang.payment', $payable->id), [
            'amount' => 400000, 'payment_date' => now()->toDateString(), 'payment_method' => 'transfer',
        ]);

        $payment = DebtPayment::first();

        $response = $this->actingAs($finance)->delete(route('debt-payments.destroy', $payment->id));
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('debt_payments', ['id' => $payment->id]);
    }

    public function test_deleting_an_approved_payment_still_reverses_the_balance(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);

        $branch = Branch::factory()->create();
        $finance = $this->makeFinanceUser($branch);
        $owner = $this->userWithRole('owner');

        $payable = Payable::create([
            'branch_id' => $branch->id, 'vendor_name' => 'PT Kain Nusantara',
            'description' => 'Pembelian bahan baku', 'amount' => 1000000,
            'paid_amount' => 0, 'status' => 'unpaid', 'created_by' => $finance->id,
        ]);

        $this->actingAs($finance)->post(route('hutang.payment', $payable->id), [
            'amount' => 400000, 'payment_date' => now()->toDateString(), 'payment_method' => 'transfer',
        ]);

        $payment = DebtPayment::first();
        $this->actingAs($owner)->post(route('approvals.approve', $payment->approvalRequest));

        $this->actingAs($finance)->delete(route('debt-payments.destroy', $payment->id))->assertRedirect();

        $payable->refresh();
        $this->assertSame(0, $payable->paid_amount);
        $this->assertSame('unpaid', $payable->status);
        $this->assertDatabaseMissing('debt_payments', ['id' => $payment->id]);
    }
}
