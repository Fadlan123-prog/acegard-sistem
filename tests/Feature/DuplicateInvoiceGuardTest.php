<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression test for the Phase 3 duplicate-invoice guard in InvoiceController::store().
 * Deliberately narrow: Customer::invoices() is hasMany (a customer can legitimately have
 * multiple invoices over time), so the guard only catches an exact-amount resubmission
 * within a short window — not "one invoice per customer ever".
 */
class DuplicateInvoiceGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_resubmitting_the_same_invoice_within_the_window_redirects_to_the_existing_one(): void
    {
        $branch   = Branch::factory()->create();
        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $staff    = $this->userWithRole('admin', $branch);

        $payload = [
            'customer_id'    => $customer->id,
            'price'          => 1500000,
            'discount'       => 0,
            'downpayment'    => 0,
            'payment_method' => 'cash',
            'status'         => 'unpaid',
            'invoice_date'   => now()->toDateString(),
        ];

        $first = $this->actingAs($staff)->post(route('invoice.store'), $payload);
        $first->assertRedirect();

        $this->assertSame(1, Invoice::where('customer_id', $customer->id)->count());

        // Double-click: identical amounts, submitted moments later.
        $second = $this->actingAs($staff)->post(route('invoice.store'), $payload);
        $second->assertRedirect();

        $this->assertSame(1, Invoice::where('customer_id', $customer->id)->count(), 'A second identical invoice should not have been created.');
    }

    public function test_a_second_invoice_with_a_different_amount_is_allowed(): void
    {
        $branch   = Branch::factory()->create();
        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $staff    = $this->userWithRole('admin', $branch);

        $base = [
            'customer_id'    => $customer->id,
            'payment_method' => 'cash',
            'status'         => 'unpaid',
            'invoice_date'   => now()->toDateString(),
        ];

        $this->actingAs($staff)->post(route('invoice.store'), $base + ['price' => 1500000]);
        $this->actingAs($staff)->post(route('invoice.store'), $base + ['price' => 2000000]);

        $this->assertSame(2, Invoice::where('customer_id', $customer->id)->count(), 'Genuinely different invoices for the same customer must not be blocked.');
    }

    public function test_an_older_invoice_with_the_same_amount_does_not_block_a_new_one(): void
    {
        $branch   = Branch::factory()->create();
        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $staff    = $this->userWithRole('admin', $branch);

        $old = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'price'       => 1500000,
            'total_price' => 1500000,
        ]);
        // Simulate it having been created well outside the dedup window.
        $old->forceFill(['created_at' => now()->subMinutes(10)])->save();

        $response = $this->actingAs($staff)->post(route('invoice.store'), [
            'customer_id'    => $customer->id,
            'price'          => 1500000,
            'payment_method' => 'cash',
            'status'         => 'unpaid',
            'invoice_date'   => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertSame(2, Invoice::where('customer_id', $customer->id)->count(), 'A returning-customer invoice outside the window must be allowed.');
    }
}
