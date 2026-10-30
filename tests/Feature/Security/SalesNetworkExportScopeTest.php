<?php

namespace Tests\Feature\Security;

use App\Exports\CustomerExport;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\SalesNetworkProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesNetworkExportScopeTest extends TestCase
{
    use RefreshDatabase;

    private function pendingProfile(Branch $branch): SalesNetworkProfile
    {
        $emp = Employee::factory()->create(['branch_id' => $branch->id, 'job_position' => 'Sales']);

        return SalesNetworkProfile::create([
            'employee_id' => $emp->id, 'referral_code' => 'REF-' . strtoupper(substr(md5((string) $emp->id), 0, 6)),
            'status' => 'pending_approval',
        ]);
    }

    public function test_admin_cannot_approve_or_reject_sales_of_other_branch(): void
    {
        $a = Branch::factory()->create();
        $b = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $a);
        $foreign = $this->pendingProfile($b);

        $this->actingAs($admin)->post(route('sales-network.approve', $foreign->id), ['password_mode' => 'invite'])->assertForbidden();
        $this->actingAs($admin)->post(route('sales-network.reject', $foreign->id))->assertForbidden();
        $this->assertDatabaseHas('sales_network_profiles', ['id' => $foreign->id, 'status' => 'pending_approval']);
    }

    public function test_pending_list_only_shows_own_branch(): void
    {
        $a = Branch::factory()->create();
        $b = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $a);
        $own = $this->pendingProfile($a);
        $foreign = $this->pendingProfile($b);

        $profiles = $this->actingAs($admin)->get(route('sales-network.pending'))->assertOk()->viewData('profiles');

        $this->assertTrue($profiles->pluck('id')->contains($own->id));
        $this->assertFalse($profiles->pluck('id')->contains($foreign->id));
    }

    public function test_customer_export_only_contains_accessible_branches(): void
    {
        $a = Branch::factory()->create();
        $b = Branch::factory()->create();
        $mine = Customer::factory()->create(['branch_id' => $a->id]);
        $theirs = Customer::factory()->create(['branch_id' => $b->id]);

        $this->actingAs($this->userWithRole('admin', $a));
        $ids = (new CustomerExport)->collection()->pluck('id');
        $this->assertTrue($ids->contains($mine->id));
        $this->assertFalse($ids->contains($theirs->id));

        $this->actingAs($this->userWithRole('owner', $a));
        $this->assertCount(2, (new CustomerExport)->collection());
    }
}
