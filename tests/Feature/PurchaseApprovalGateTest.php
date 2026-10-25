<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Wires the generic approval system (Phase 1) into the real, pre-existing
 * PurchaseController: confirming a purchase ('draft' -> 'confirmed') now
 * requires Owner approval (see Purchase::onApprovalDecision()), while every
 * other pre-existing status transition/guard is untouched.
 */
class PurchaseApprovalGateTest extends TestCase
{
    use RefreshDatabase;

    private function makePurchasingUser(Branch $branch): User
    {
        $user = $this->userWithRole('purchasing', $branch);

        return $user;
    }

    private function itemPayload(): array
    {
        return [
            'items' => [
                ['item_name' => 'Kain PPF', 'qty' => 2, 'unit' => 'roll', 'unit_price' => 500000],
            ],
        ];
    }

    public function test_creating_a_purchase_as_confirmed_is_saved_as_draft_pending_owner_approval(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);

        $branch = Branch::factory()->create();
        $purchasing = $this->makePurchasingUser($branch);
        $supplier = Supplier::create(['name' => 'PT Sumber Jaya']);

        $response = $this->actingAs($purchasing)->post(route('purchases.store'), array_merge([
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'status' => 'confirmed',
        ], $this->itemPayload()));

        $response->assertRedirect(route('purchases.index'));

        $purchase = Purchase::first();
        $this->assertSame('draft', $purchase->status);
        $this->assertNotNull($purchase->approvalRequest);
        $this->assertSame('pending', $purchase->approvalRequest->status);
    }

    public function test_owner_approval_flips_the_purchase_to_confirmed(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);

        $branch = Branch::factory()->create();
        $purchasing = $this->makePurchasingUser($branch);
        $owner = $this->userWithRole('owner');
        $supplier = Supplier::create(['name' => 'PT Sumber Jaya']);

        $this->actingAs($purchasing)->post(route('purchases.store'), array_merge([
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'status' => 'confirmed',
        ], $this->itemPayload()));

        $purchase = Purchase::first();

        $this->actingAs($owner)
            ->post(route('approvals.approve', $purchase->approvalRequest))
            ->assertRedirect();

        $purchase->refresh();
        $this->assertSame('confirmed', $purchase->status);
    }

    public function test_owner_rejection_leaves_the_purchase_as_draft(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);

        $branch = Branch::factory()->create();
        $purchasing = $this->makePurchasingUser($branch);
        $owner = $this->userWithRole('owner');
        $supplier = Supplier::create(['name' => 'PT Sumber Jaya']);

        $this->actingAs($purchasing)->post(route('purchases.store'), array_merge([
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'status' => 'confirmed',
        ], $this->itemPayload()));

        $purchase = Purchase::first();

        $this->actingAs($owner)
            ->post(route('approvals.reject', $purchase->approvalRequest), ['note' => 'Harga terlalu tinggi.'])
            ->assertRedirect();

        $purchase->refresh();
        $this->assertSame('draft', $purchase->status);
        $this->assertSame('rejected', $purchase->approvalRequest->status);
    }

    public function test_confirming_an_existing_draft_purchase_via_update_requires_approval_too(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);

        $branch = Branch::factory()->create();
        $purchasing = $this->makePurchasingUser($branch);
        $supplier = Supplier::create(['name' => 'PT Sumber Jaya']);

        $this->actingAs($purchasing)->post(route('purchases.store'), array_merge([
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'status' => 'draft',
        ], $this->itemPayload()));

        $purchase = Purchase::first();
        $this->assertNull($purchase->approvalRequest);

        $this->actingAs($purchasing)->put(route('purchases.update', $purchase), array_merge([
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'status' => 'confirmed',
        ], $this->itemPayload()))->assertRedirect(route('purchases.index'));

        $purchase->refresh();
        $this->assertSame('draft', $purchase->status);
        $this->assertNotNull($purchase->approvalRequest);
        $this->assertSame('pending', $purchase->approvalRequest->status);
    }

    public function test_cannot_resubmit_a_purchase_that_already_has_a_pending_approval(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);

        $branch = Branch::factory()->create();
        $purchasing = $this->makePurchasingUser($branch);
        $supplier = Supplier::create(['name' => 'PT Sumber Jaya']);

        $this->actingAs($purchasing)->post(route('purchases.store'), array_merge([
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'status' => 'confirmed',
        ], $this->itemPayload()));

        $purchase = Purchase::first();

        $response = $this->actingAs($purchasing)->put(route('purchases.update', $purchase), array_merge([
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'status' => 'confirmed',
        ], $this->itemPayload()));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('approval_requests', 1);
    }

    public function test_resubmitting_after_rejection_replaces_the_old_approval_request(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);

        $branch = Branch::factory()->create();
        $purchasing = $this->makePurchasingUser($branch);
        $owner = $this->userWithRole('owner');
        $supplier = Supplier::create(['name' => 'PT Sumber Jaya']);

        $this->actingAs($purchasing)->post(route('purchases.store'), array_merge([
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'status' => 'confirmed',
        ], $this->itemPayload()));

        $purchase = Purchase::first();
        $this->actingAs($owner)->post(route('approvals.reject', $purchase->approvalRequest), ['note' => 'Salah harga.']);

        $purchase->refresh();
        $this->actingAs($purchasing)->put(route('purchases.update', $purchase), array_merge([
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'status' => 'confirmed',
        ], $this->itemPayload()))->assertRedirect(route('purchases.index'));

        $purchase->refresh();
        $this->assertDatabaseCount('approval_requests', 1);
        $this->assertSame('pending', $purchase->approvalRequest->status);
    }

    public function test_saving_as_plain_draft_or_cancelled_is_unaffected_by_the_approval_gate(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);

        $branch = Branch::factory()->create();
        $purchasing = $this->makePurchasingUser($branch);
        $supplier = Supplier::create(['name' => 'PT Sumber Jaya']);

        $this->actingAs($purchasing)->post(route('purchases.store'), array_merge([
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'status' => 'draft',
        ], $this->itemPayload()));

        $purchase = Purchase::first();
        $this->assertSame('draft', $purchase->status);
        $this->assertNull($purchase->approvalRequest);

        $this->actingAs($purchasing)->put(route('purchases.update', $purchase), array_merge([
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'status' => 'cancelled',
        ], $this->itemPayload()))->assertRedirect(route('purchases.index'));

        $purchase->refresh();
        $this->assertSame('cancelled', $purchase->status);
        $this->assertNull($purchase->approvalRequest);
    }
}
