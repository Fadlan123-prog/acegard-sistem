<?php

namespace Tests\Feature\Security;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeClaim;
use App\Models\LeaveRequest;
use App\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Shift, cuti, dan pencairan klaim terikat cabang untuk admin; owner lintas cabang. */
class HrBranchScopeTest extends TestCase
{
    use RefreshDatabase;

    private Branch $a;
    private Branch $b;

    protected function setUp(): void
    {
        parent::setUp();
        $this->a = Branch::factory()->create();
        $this->b = Branch::factory()->create();
    }

    private function shift(Branch $branch): Shift
    {
        return Shift::create([
            'branch_id' => $branch->id, 'name' => 'Pagi', 'start_time' => '08:00',
            'end_time' => '16:00', 'overtime_after_minutes' => 0, 'is_active' => true,
        ]);
    }

    private function leave(Branch $branch): LeaveRequest
    {
        $emp = Employee::factory()->create(['branch_id' => $branch->id]);

        return LeaveRequest::create([
            'employee_id' => $emp->id, 'branch_id' => $branch->id, 'leave_type' => 'annual',
            'from_date' => '2026-11-01', 'to_date' => '2026-11-01', 'days' => 1,
            'reason' => 'x', 'status' => 'pending',
        ]);
    }

    public function test_shift_of_other_branch_is_protected(): void
    {
        $admin = $this->userWithRole('admin', $this->a);
        $foreign = $this->shift($this->b);

        $this->actingAs($admin)->get(route('shifts.edit', $foreign->id))->assertForbidden();
        $this->actingAs($admin)->delete(route('shifts.destroy', $foreign->id))->assertForbidden();
        $this->assertDatabaseHas('shifts', ['id' => $foreign->id]);

        $this->actingAs($admin)->post(route('shifts.store'), [
            'branch_id' => $this->b->id, 'name' => 'X', 'start_time' => '08:00',
            'end_time' => '16:00', 'overtime_after_minutes' => 0,
        ])->assertForbidden();

        $own = $this->shift($this->a);
        $this->actingAs($admin)->get(route('shifts.edit', $own->id))->assertOk();
    }

    public function test_leave_of_other_branch_cannot_be_decided(): void
    {
        $admin = $this->userWithRole('admin', $this->a);
        $foreign = $this->leave($this->b);

        $this->actingAs($admin)->put(route('leave-requests.approve', $foreign->id))->assertForbidden();
        $this->actingAs($admin)->put(route('leave-requests.reject', $foreign->id))->assertForbidden();
        $this->actingAs($admin)->get(route('leave-requests.show', $foreign->id))->assertForbidden();
        $this->assertSame('pending', $foreign->fresh()->status);

        $this->actingAs($admin)->put(route('leave-requests.approve', $this->leave($this->a)->id))->assertRedirect();
    }

    public function test_claim_of_other_branch_cannot_be_disbursed_but_owner_can(): void
    {
        $emp = Employee::factory()->create(['branch_id' => $this->b->id]);
        $claim = EmployeeClaim::create([
            'employee_id' => $emp->id, 'branch_id' => $this->b->id, 'type' => 'bensin',
            'amount' => 50000, 'status' => 'approved',
        ]);

        $finance = $this->userWithRole('finance', $this->a);
        $this->actingAs($finance)->post(route('claims-disbursement.disburse', $claim->id))->assertForbidden();
        $this->assertSame('approved', $claim->fresh()->status);
    }
}
