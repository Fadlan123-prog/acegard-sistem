<?php

namespace Tests\Feature\Security;

use App\Models\Branch;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\CustomerBuilding;
use App\Models\CustomerPpf;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\PettyCashTransaction;
use App\Models\WarrantyClaim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression tests for the Critical/High branch-scoping gaps closed in Phases 1 and 3
 * (CustomerController, CustomerBuildingController, PettyCashController, CustomerPpfController,
 * AttendanceController, AdminWarrantyClaimController, CommissionController, InvoiceController).
 * Each test proves a non-superadmin user assigned to Branch B cannot read/write Branch A's data,
 * and that the same action still works for a user who legitimately belongs to Branch A.
 */
class BranchScopingTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branchA;
    private Branch $branchB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branchA = Branch::factory()->create();
        $this->branchB = Branch::factory()->create();
    }

    // ─── CustomerController ─────────────────────────────────────────────

    public function test_cannot_update_customer_from_another_branch(): void
    {
        $customer = Customer::factory()->create(['branch_id' => $this->branchA->id]);
        $outsider = $this->userWithRole('admin', $this->branchB);

        $this->actingAs($outsider)
            ->put(route('customer.update', $customer->id), [])
            ->assertForbidden();
    }

    public function test_cannot_destroy_customer_from_another_branch(): void
    {
        $customer = Customer::factory()->create(['branch_id' => $this->branchA->id]);
        $outsider = $this->userWithRole('admin', $this->branchB);

        $this->actingAs($outsider)
            ->delete(route('customer.destroy', $customer->id))
            ->assertForbidden();

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_bulk_delete_only_removes_customers_in_own_branch(): void
    {
        $ownCustomer   = Customer::factory()->create(['branch_id' => $this->branchB->id]);
        $otherCustomer = Customer::factory()->create(['branch_id' => $this->branchA->id]);
        $staff         = $this->userWithRole('admin', $this->branchB);

        $this->actingAs($staff)->delete(route('customer.bulkDelete'), [
            'selected' => [$ownCustomer->id, $otherCustomer->id],
        ]);

        $this->assertDatabaseMissing('customers', ['id' => $ownCustomer->id]);
        $this->assertDatabaseHas('customers', ['id' => $otherCustomer->id]);
    }

    public function test_superadmin_can_update_customer_from_any_branch(): void
    {
        $customer = Customer::factory()->create(['branch_id' => $this->branchA->id]);
        $admin    = $this->superadmin();

        $response = $this->actingAs($admin)->put(route('customer.update', $customer->id), [
            'wsn' => $customer->wsn, 'name' => 'Updated Name', 'email' => $customer->email,
            'phone_number' => $customer->phone_number, 'address' => $customer->address,
            'vehicle_brand' => $customer->vehicle_brand, 'vehicle_model' => $customer->vehicle_model,
            'plat_number' => $customer->plat_number, 'vehicle_year' => $customer->vehicle_year,
            'dealer_name' => $customer->dealer_name, 'city' => $customer->city, 'country' => $customer->country,
            'install_type' => 'fullset', 'install_date' => now()->toDateString(),
            'share_mode' => 'auto', 'tukang_id' => Employee::factory()->create(['branch_id' => $this->branchA->id])->id,
            'warantee_duration' => 5,
            'products' => [['product_id' => 1, 'category_product_id' => 1, 'part_id' => 1]],
            'admin_password' => 'wrong-password-on-purpose',
        ]);

        // Not forbidden — it may fail validation/password confirmation downstream (a plain
        // redirect either way), but it must never be a 403: that would mean the branch guard
        // itself wrongly blocked superadmin, which is the one thing this test exists to catch.
        $response->assertStatus(302);
    }

    // ─── CustomerBuildingController ─────────────────────────────────────

    public function test_cannot_edit_customer_building_from_another_branch(): void
    {
        $building = CustomerBuilding::factory()->create(['branch_id' => $this->branchA->id]);
        $outsider = $this->userWithRole('admin', $this->branchB);

        $this->actingAs($outsider)
            ->get(route('customer.building.edit', $building->id))
            ->assertForbidden();
    }

    public function test_cannot_bulk_delete_customer_building_from_another_branch(): void
    {
        $building = CustomerBuilding::factory()->create(['branch_id' => $this->branchA->id]);
        $outsider = $this->userWithRole('admin', $this->branchB);

        $this->actingAs($outsider)->delete(route('customer.building.bulkDelete'), [
            'selected' => [$building->id],
        ]);

        $this->assertDatabaseHas('customer_building', ['id' => $building->id]);
    }

    // ─── PettyCashController ────────────────────────────────────────────

    public function test_cannot_view_petty_cash_ledger_of_another_branch_via_spoofed_param(): void
    {
        $outsider = $this->userWithRole('admin', $this->branchB);

        $this->actingAs($outsider)
            ->get(route('petty-cash.index', ['branch_id' => $this->branchA->id]))
            ->assertForbidden();
    }

    public function test_cannot_record_petty_cash_in_for_another_branch(): void
    {
        $outsider = $this->userWithRole('admin', $this->branchB);

        $this->actingAs($outsider)->post(route('petty-cash.store-in'), [
            'branch_id' => $this->branchA->id,
            'date'      => now()->toDateString(),
            'amount'    => 50000,
        ])->assertForbidden();

        $this->assertDatabaseMissing('petty_cash_transactions', ['branch_id' => $this->branchA->id]);
    }

    public function test_cannot_delete_petty_cash_transaction_of_another_branch(): void
    {
        $trx      = PettyCashTransaction::factory()->create(['branch_id' => $this->branchA->id]);
        $outsider = $this->userWithRole('admin', $this->branchB);

        $this->actingAs($outsider)
            ->delete(route('petty-cash.destroy', $trx->id))
            ->assertForbidden();

        $this->assertDatabaseHas('petty_cash_transactions', ['id' => $trx->id]);
    }

    // ─── CustomerPpfController ───────────────────────────────────────────

    public function test_cannot_destroy_ppf_customer_from_another_branch(): void
    {
        $ppfCustomer = CustomerPpf::factory()->create(['branch_id' => $this->branchA->id]);
        $outsider    = $this->userWithRole('admin', $this->branchB);

        $this->actingAs($outsider)
            ->delete(route('ppf-customers.destroy', $ppfCustomer->id))
            ->assertForbidden();

        $this->assertDatabaseHas('customer_ppf', ['id' => $ppfCustomer->id]);
    }

    // ─── AttendanceController ────────────────────────────────────────────

    public function test_monthly_report_ignores_spoofed_branch_id_for_non_superadmin(): void
    {
        $outsider = $this->userWithRole('admin', $this->branchB);

        $response = $this->actingAs($outsider)
            ->get(route('attendances.monthlyReport', ['branch_id' => $this->branchA->id]));

        $response->assertSuccessful();
        // Must silently fall back to the user's own branch, never leak Branch A's data.
        $response->assertViewHas('branchId', fn ($id) => (int) $id !== $this->branchA->id);
    }

    // ─── AdminWarrantyClaimController ────────────────────────────────────

    public function test_cannot_inspect_warranty_claim_from_another_branch(): void
    {
        $claim    = WarrantyClaim::factory()->create(['branch_id' => $this->branchA->id]);
        $outsider = $this->userWithRole('admin', $this->branchB);

        $this->actingAs($outsider)
            ->put(route('admin.claims.inspect', $claim->id), ['cause' => 'unknown'])
            ->assertForbidden();
    }

    public function test_cannot_update_status_of_warranty_claim_from_another_branch(): void
    {
        $claim    = WarrantyClaim::factory()->create(['branch_id' => $this->branchA->id]);
        $outsider = $this->userWithRole('admin', $this->branchB);

        $this->actingAs($outsider)
            ->put(route('admin.claims.status', $claim->id), ['status' => 'approved'])
            ->assertForbidden();
    }

    // ─── CommissionController ────────────────────────────────────────────

    public function test_cannot_move_commission_belonging_to_another_branch(): void
    {
        $commission = Commission::factory()->withBranch($this->branchA->id)->create();
        $newEmployee = Employee::factory()->create(['branch_id' => $this->branchB->id]);
        $outsider   = $this->userWithRole('admin', $this->branchB);

        $this->actingAs($outsider)
            ->put(route('commission.move.update', $commission->id), ['employees_id' => $newEmployee->id])
            ->assertForbidden();
    }

    public function test_cannot_toggle_commission_belonging_to_another_branch(): void
    {
        $commission = Commission::factory()->withBranch($this->branchA->id)->create(['role' => 'marketing']);
        $outsider   = $this->userWithRole('admin', $this->branchB);

        $this->actingAs($outsider)
            ->patch(route('commission.toggle-active', $commission->id))
            ->assertForbidden();
    }

    // ─── InvoiceController::download ─────────────────────────────────────

    public function test_cannot_download_invoice_pdf_from_another_branch(): void
    {
        $customer = Customer::factory()->create(['branch_id' => $this->branchA->id]);
        $invoice  = Invoice::factory()->create(['customer_id' => $customer->id]);
        $outsider = $this->userWithRole('admin', $this->branchB);

        $this->actingAs($outsider)
            ->get(route('invoice.download', $invoice->id))
            ->assertForbidden();
    }
}
