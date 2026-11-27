<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CustomerBuilding;
use App\Models\InvoiceBuilding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Guard invoice gedung: invoice lunas tidak boleh dihapus; pelunasan tanpa produk memberi peringatan jujur. */
class InvoiceBuildingGuardTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branch = Branch::factory()->create();
    }

    private function invoice(string $status = 'unpaid'): InvoiceBuilding
    {
        $customer = CustomerBuilding::factory()->create(['branch_id' => $this->branch->id]);
        $user = User::factory()->create();

        return InvoiceBuilding::create([
            'customer_building_id' => $customer->id, 'branch_id' => $this->branch->id, 'user_id' => $user->id,
            'type' => 'new', 'price' => 5000000, 'discount' => 0, 'down_payment' => 0, 'total_price' => 5000000,
            'remaining_payment' => $status === 'paid' ? 0 : 5000000, 'status' => $status,
            'pelunasan_at' => $status === 'paid' ? now() : null,
            'invoice_number' => 'IB-' . uniqid(), 'invoice_date' => now()->toDateString(),
        ]);
    }

    public function test_paid_invoice_cannot_be_deleted(): void
    {
        $invoice = $this->invoice('paid');
        $admin = $this->userWithRole('admin', $this->branch);

        $this->actingAs($admin)->delete(route('invoice.building.destroy', $invoice->id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('invoice_building', ['id' => $invoice->id]);
    }

    public function test_unpaid_invoice_can_still_be_deleted(): void
    {
        $invoice = $this->invoice();
        $admin = $this->userWithRole('admin', $this->branch);

        $this->actingAs($admin)->delete(route('invoice.building.destroy', $invoice->id))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('invoice_building', ['id' => $invoice->id]);
    }

    public function test_pelunasan_without_products_warns_instead_of_claiming_stock_was_deducted(): void
    {
        $invoice = $this->invoice();
        $admin = $this->userWithRole('admin', $this->branch);

        $this->actingAs($admin)->post(route('invoice.building.pelunasan', $invoice->id))
            ->assertSessionHas('warning')
            ->assertSessionMissing('success');

        $this->assertNotNull($invoice->fresh()->pelunasan_at);
        $this->assertNull($invoice->fresh()->stock_deducted_at);
    }
}
