<?php

namespace Tests\Feature\Security;

use App\Models\Branch;
use App\Models\CustomerPpf;
use App\Models\PpfInvoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpfInvoiceGuardTest extends TestCase
{
    use RefreshDatabase;

    private function invoice(Branch $branch): PpfInvoice
    {
        $customer = CustomerPpf::factory()->create(['branch_id' => $branch->id]);

        $productId = \DB::table('ppf_products')->insertGetId([
            'name' => 'P', 'brand' => 'B', 'thickness_microns' => 150, 'finish' => 'gloss',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $installation = \App\Models\CustomerPpfInstallation::create([
            'customer_ppf_id' => $customer->id, 'ppf_product_id' => $productId, 'branch_id' => $branch->id,
            'install_type' => 'full_body', 'install_date' => now()->toDateString(), 'warranty_code' => 'W-1', 'warranty_start' => now()->toDateString(), 'warranty_years' => 5, 'warranty_end' => now()->addYears(5)->toDateString(),
        ]);

        return PpfInvoice::create([
            'branch_id' => $branch->id, 'customer_ppf_id' => $customer->id, 'customer_ppf_installation_id' => $installation->id,
            'invoice_number' => 'PPF-T-0001', 'invoice_date' => now()->toDateString(), 'payment_method' => 'cash',
            'price' => 5000000, 'discount' => 0, 'downpayment' => 0, 'total_price' => 5000000,
            'remaining_payment' => 5000000, 'status' => 'unpaid',
        ]);
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'invoice_date' => now()->toDateString(), 'payment_method' => 'cash',
            'price' => 5000000, 'discount' => 0, 'downpayment' => 0, 'status' => 'unpaid',
        ], $over);
    }

    public function test_cannot_mark_paid_with_outstanding_balance_or_oversized_discount(): void
    {
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $inv = $this->invoice($branch);

        $this->actingAs($admin)->put(route('ppf-invoices.update', $inv->id), $this->payload(['status' => 'paid']))
            ->assertSessionHasErrors('status');
        $this->actingAs($admin)->put(route('ppf-invoices.update', $inv->id), $this->payload(['discount' => 9000000]))
            ->assertSessionHasErrors('discount');

        $this->assertSame('unpaid', $inv->fresh()->status);

        $this->actingAs($admin)->put(route('ppf-invoices.update', $inv->id), $this->payload(['status' => 'paid', 'downpayment' => 5000000]))
            ->assertRedirect();
        $this->assertSame('paid', $inv->fresh()->status);
    }
}
