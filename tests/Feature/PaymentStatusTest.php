<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerBuilding;
use App\Models\Invoice;
use App\Models\InvoiceBuilding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentStatusTest extends TestCase
{
    use RefreshDatabase;

    private function carInvoice(Branch $branch, string $name, array $over = []): Invoice
    {
        $c = Customer::factory()->create(['branch_id' => $branch->id, 'name' => $name]);

        return Invoice::factory()->create(array_merge(['customer_id' => $c->id], $over));
    }

    public function test_finance_sees_unpaid_by_default_and_can_filter_paid(): void
    {
        $branch = Branch::factory()->create();
        $finance = $this->userWithRole('finance', $branch);

        $this->carInvoice($branch, 'Belum Bayar Budi');
        $this->carInvoice($branch, 'Sebagian Sari', ['downpayment' => 500000, 'remaining_payment' => 1000000, 'status' => 'partial']);
        $this->carInvoice($branch, 'Lunas Lina', ['status' => 'paid', 'pelunasan_at' => now(), 'remaining_payment' => 0]);

        $default = $this->actingAs($finance)->get(route('finance.payment-status'))->assertOk();
        $names = collect($default->viewData('rows')->items())->pluck('customer_name');
        $this->assertTrue($names->contains('Belum Bayar Budi'));
        $this->assertTrue($names->contains('Sebagian Sari'));
        $this->assertFalse($names->contains('Lunas Lina'));

        $paid = $this->actingAs($finance)->get(route('finance.payment-status', ['status' => 'paid']))->viewData('rows')->items();
        $this->assertSame(['Lunas Lina'], collect($paid)->pluck('customer_name')->all());

        $summary = $default->viewData('summary');
        $this->assertSame(1, (int) $summary['paid']->n);
        $this->assertSame(1, (int) $summary['partial']->n);
        $this->assertSame(1, (int) $summary['unpaid']->n);
    }

    public function test_building_invoices_are_included_and_branch_is_scoped(): void
    {
        $branch = Branch::factory()->create();
        $other = Branch::factory()->create();
        $finance = $this->userWithRole('finance', $branch);

        $mk = function (Branch $b, string $name) {
            $c = CustomerBuilding::factory()->create(['branch_id' => $b->id, 'name' => $name]);

            return InvoiceBuilding::create([
                'customer_building_id' => $c->id, 'branch_id' => $b->id, 'user_id' => User::factory()->create()->id,
                'type' => 'new', 'price' => 1000000, 'discount' => 0, 'down_payment' => 0, 'total_price' => 1000000,
                'remaining_payment' => 1000000, 'status' => 'unpaid', 'invoice_number' => 'IB-' . uniqid(), 'invoice_date' => now()->toDateString(),
            ]);
        };
        $mk($branch, 'Gedung Cabang Saya');
        $mk($other, 'Gedung Cabang Lain');

        $rows = $this->actingAs($finance)->get(route('finance.payment-status', ['type' => 'building']))->viewData('rows')->items();

        $this->assertSame(['Gedung Cabang Saya'], collect($rows)->pluck('customer_name')->all());
    }

    public function test_roles_without_finance_access_are_blocked(): void
    {
        $branch = Branch::factory()->create();

        $this->actingAs($this->userWithRole('teknisi'))->get(route('finance.payment-status'))->assertForbidden();
        $this->actingAs($this->userWithRole('marketing', $branch))->get(route('finance.payment-status'))->assertForbidden();
    }
}
