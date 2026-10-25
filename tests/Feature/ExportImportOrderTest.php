<?php

namespace Tests\Feature;

use App\Models\ExportOrder;
use App\Models\ImportOrder;
use App\Models\InternationalPartner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportImportOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_order_totals_are_computed_correctly_from_items_and_costs(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $user = $this->userWithRole('export_import');

        $supplier = InternationalPartner::create([
            'type' => 'supplier', 'name' => 'Korea Film Co', 'country' => 'South Korea', 'default_currency' => 'USD',
        ]);

        $response = $this->actingAs($user)->post(route('import-orders.store'), [
            'international_partner_id' => $supplier->id,
            'currency'                 => 'USD',
            'exchange_rate'            => 15800,
            'duty_cost'                => 2000000,
            'tax_cost'                 => 1500000,
            'freight_cost'             => 3000000,
            'insurance_cost'           => 500000,
            'order_date'               => now()->toDateString(),
            'items' => [
                ['item_name' => 'PPF Roll 1.52m', 'quantity' => 10, 'unit' => 'roll', 'unit_price_foreign' => 200],
                ['item_name' => 'PPF Roll 1.8m',   'quantity' => 5,  'unit' => 'roll', 'unit_price_foreign' => 300],
            ],
        ]);

        $response->assertRedirect();

        $order = ImportOrder::first();
        $this->assertNotNull($order);
        $this->assertSame('ordered', $order->status);
        $this->assertSame(2, $order->items()->count());

        // subtotal_foreign = 10*200 + 5*300 = 3500
        $this->assertSame(3500, (int) $order->subtotal_foreign);

        // total_cost_idr = 3500*15800 + 2,000,000 + 1,500,000 + 3,000,000 + 500,000
        $expected = (int) round(3500 * 15800) + 2000000 + 1500000 + 3000000 + 500000;
        $this->assertSame($expected, $order->total_cost_idr);

        // Status transition
        $this->actingAs($user)->put(route('import-orders.status', $order), ['status' => 'received'])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('received', $order->status);
        $this->assertNotNull($order->received_date);
    }

    public function test_export_order_totals_are_computed_correctly(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $user = $this->userWithRole('export_import');

        $buyer = InternationalPartner::create([
            'type' => 'buyer', 'name' => 'Aussie Auto Parts', 'country' => 'Australia', 'default_currency' => 'AUD',
        ]);

        $response = $this->actingAs($user)->post(route('export-orders.store'), [
            'international_partner_id' => $buyer->id,
            'currency'                 => 'AUD',
            'exchange_rate'            => 10500,
            'order_date'               => now()->toDateString(),
            'items' => [
                ['item_name' => 'PPF Finished Kit', 'quantity' => 20, 'unit' => 'kit', 'unit_price_foreign' => 150],
            ],
        ]);

        $response->assertRedirect();

        $order = ExportOrder::first();
        $this->assertSame(3000, (int) $order->subtotal_foreign); // 20*150
        $this->assertSame((int) round(3000 * 10500), $order->total_revenue_idr);
        $this->assertSame('confirmed', $order->status);
    }

    public function test_non_export_import_role_cannot_access_the_module(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $marketing = $this->userWithRole('marketing');

        $this->actingAs($marketing)->get(route('import-orders.index'))->assertForbidden();
    }
}
