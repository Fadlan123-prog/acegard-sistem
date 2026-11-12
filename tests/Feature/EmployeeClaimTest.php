<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeClaim;
use App\Models\EmployeeHealthBalance;
use App\Models\PettyCashTransaction;
use App\Models\User;
use App\Notifications\ApprovalRequestDecided;
use App\Notifications\ApprovalRequestSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * End-to-end test of the generic approval system (Phase 1) wired through the
 * real employee_claim flow (Phase 3): submit -> Manager Operasional approves
 * (1 step; approver harus satu cabang dengan pengaju) -> Finance disburses via
 * Petty Cash. Also covers rejection and the health-balance deduction path.
 */
class EmployeeClaimTest extends TestCase
{
    use RefreshDatabase;

    private function makeTeknisiUser(Branch $branch): array
    {
        $employee = Employee::factory()->create(['branch_id' => $branch->id, 'job_position' => 'Teknisi']);
        $user = User::factory()->create(['employee_id' => $employee->id]);
        $user->assignRole('teknisi');

        return [$user, $employee];
    }

    public function test_pembelian_claim_full_lifecycle_submit_approve_disburse(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);

        $branch = Branch::factory()->create();
        [$teknisi, $employee] = $this->makeTeknisiUser($branch);
        $manager = $this->userWithRole('manager_operasional', $branch);
        $finance = $this->userWithRole('finance', $branch);

        Notification::fake();

        // 1. Submit
        $response = $this->actingAs($teknisi)->post(route('my-claims.store'), [
            'type'        => 'pembelian',
            'amount'      => 150000,
            'description' => 'Beli lakban & kain lap',
        ]);
        $response->assertRedirect(route('my-claims.index'));

        $claim = EmployeeClaim::first();
        $this->assertSame('pending_approval', $claim->status);
        $this->assertNotNull($claim->approvalRequest);
        $this->assertSame('pending', $claim->approvalRequest->status);

        Notification::assertSentTo($manager, ApprovalRequestSubmitted::class);

        // 2. Manager approves — company-wide, no branch assignment needed on $manager
        $approvalRequest = $claim->approvalRequest;
        $this->actingAs($manager)->post(route('approvals.approve', $approvalRequest))
            ->assertRedirect();

        $claim->refresh();
        $this->assertSame('approved', $claim->status);
        $this->assertSame('approved', $claim->approvalRequest->status);
        Notification::assertSentTo($teknisi, ApprovalRequestDecided::class);

        // 3. Finance disburses — creates a real PettyCashTransaction, not an auto-silent one
        $this->assertDatabaseCount('petty_cash_transactions', 0);

        $this->actingAs($finance)->post(route('claims-disbursement.disburse', $claim))
            ->assertRedirect();

        $claim->refresh();
        $this->assertSame('paid', $claim->status);
        $this->assertNotNull($claim->petty_cash_transaction_id);

        $transaction = PettyCashTransaction::find($claim->petty_cash_transaction_id);
        $this->assertSame('out', $transaction->type);
        $this->assertSame(150000, $transaction->amount);
        $this->assertSame($employee->id, $transaction->employee_id);
        $this->assertSame($branch->id, $transaction->branch_id);
    }

    public function test_manager_rejection_stops_the_claim_before_finance_sees_it(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);

        $branch = Branch::factory()->create();
        [$teknisi] = $this->makeTeknisiUser($branch);
        $manager = $this->userWithRole('manager_operasional', $branch);
        $finance = $this->userWithRole('finance', $branch);

        $this->actingAs($teknisi)->post(route('my-claims.store'), [
            'type'   => 'bensin',
            'amount' => 50000,
        ]);

        $claim = EmployeeClaim::first();

        $this->actingAs($manager)->post(route('approvals.reject', $claim->approvalRequest), [
            'note' => 'Tidak ada bukti perjalanan dinas.',
        ])->assertRedirect();

        $claim->refresh();
        $this->assertSame('rejected', $claim->status);

        // Never shows up in Finance's disbursement queue
        $response = $this->actingAs($finance)->get(route('claims-disbursement.index'));
        $response->assertDontSee('bensin', false);
    }

    public function test_kesehatan_claim_deducts_health_balance_only_after_approval_not_on_submit(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);

        $branch = Branch::factory()->create();
        [$teknisi, $employee] = $this->makeTeknisiUser($branch);
        $manager = $this->userWithRole('manager_operasional', $branch);

        EmployeeHealthBalance::create([
            'employee_id'      => $employee->id,
            'year'             => now()->year,
            'allocated_amount' => 2000000,
            'used_amount'      => 0,
        ]);

        $this->actingAs($teknisi)->post(route('my-claims.store'), [
            'type'   => 'kesehatan',
            'amount' => 500000,
        ])->assertRedirect();

        $balance = EmployeeHealthBalance::where('employee_id', $employee->id)->first();
        $this->assertSame(0, $balance->used_amount, 'Balance must not move until approval, not just on submit.');

        $claim = EmployeeClaim::first();
        $this->actingAs($manager)->post(route('approvals.approve', $claim->approvalRequest));

        $balance->refresh();
        $this->assertSame(500000, $balance->used_amount);
        $this->assertSame(1500000, $balance->remaining());
    }

    public function test_kesehatan_claim_over_remaining_balance_is_rejected_at_submission(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);

        $branch = Branch::factory()->create();
        [$teknisi, $employee] = $this->makeTeknisiUser($branch);

        EmployeeHealthBalance::create([
            'employee_id'      => $employee->id,
            'year'             => now()->year,
            'allocated_amount' => 100000,
            'used_amount'      => 0,
        ]);

        $response = $this->actingAs($teknisi)->post(route('my-claims.store'), [
            'type'   => 'kesehatan',
            'amount' => 500000,
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('employee_claims', 0);
    }
}
