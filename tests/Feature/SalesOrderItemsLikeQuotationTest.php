<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Quotation;
use App\Models\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Rincian harga Sales Order sama seperti quotation: paket mobil (bagian/tipe/kegelapan) + kendaraan per paket. */
class SalesOrderItemsLikeQuotationTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalog(): void
    {
        DB::table('parts')->insert([
            ['id' => 1, 'name' => 'WINDSHIELD', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'FIRST ROW SIDE WINDOW', 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('category_products')->insert(['id' => 1, 'name' => 'NOTCH UV 400', 'duration' => 5, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('products')->insert(['id' => 1, 'category_id' => 1, 'name' => 'NV70', 'description' => '20%', 'created_at' => now(), 'updated_at' => now()]);
    }

    private function payload(Branch $branch, array $item, string $category = 'car_film'): array
    {
        return [
            'category' => $category, 'prospect_name' => 'Budi', 'prospect_phone' => '0812', 'date' => now()->toDateString(),
            'discount_amount' => 0, 'branch_id' => $branch->id,
            'items' => [array_merge(['unit' => 'paket', 'quantity' => 1, 'unit_price' => 2500000, 'discount_percent' => 0], $item)],
        ];
    }

    public function test_sales_order_stores_the_car_package_with_vehicle_and_forces_one_package(): void
    {
        $this->seedCatalog();
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);

        $this->actingAs($admin)->post(route('sales-orders.store'), $this->payload($branch, [
            'description' => 'Fullset', 'vehicle_label' => 'Toyota Avanza', 'vehicle_plate' => 'b 1234 xy',
            'quantity' => 4, 'discount_percent' => 30, 'specifications' => 'palsu',
            'film_lines' => [['part_id' => 1, 'category_id' => 1, 'product_id' => 1], ['part_id' => 2, 'category_id' => 1, 'product_id' => 1]],
        ]))->assertSessionHasNoErrors();

        $item = \App\Models\SalesOrderItem::first();
        $this->assertSame('Fullset', $item->description);
        $this->assertSame('Kaca Depan, Samping Baris 1: NOTCH UV 400 NV70 (kegelapan 20%)', $item->specifications);
        $this->assertSame('paket', $item->unit);
        $this->assertSame(1.0, (float) $item->quantity);
        $this->assertSame(0.0, (float) $item->discount_percent);
        $this->assertSame(['Toyota Avanza', 'B 1234 XY'], [$item->vehicle_label, $item->vehicle_plate]);
        $this->assertCount(2, $item->film_lines);

        $so = SalesOrder::first();
        $this->actingAs($admin)->get(route('sales-orders.show', $so))->assertSee('Toyota Avanza · B 1234 XY');
    }

    public function test_sales_order_rejects_darkness_that_belongs_to_another_type(): void
    {
        $this->seedCatalog();
        DB::table('category_products')->insert(['id' => 2, 'name' => 'BLACK MASTER', 'duration' => 5, 'created_at' => now(), 'updated_at' => now()]);
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);

        $this->actingAs($admin)->post(route('sales-orders.store'), $this->payload($branch, [
            'film_lines' => [['part_id' => 1, 'category_id' => 2, 'product_id' => 1]],
        ]))->assertSessionHasErrors('items.0.film_lines.0.product_id');

        $this->assertSame(0, SalesOrder::count());
    }

    public function test_building_sales_order_keeps_a_plain_item(): void
    {
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);

        $this->actingAs($admin)->post(route('sales-orders.store'), $this->payload($branch, [
            'description' => 'FLEX ONE WAY — Silver Grey', 'specifications' => 'Kegelapan 80%', 'unit' => 'meter', 'quantity' => 12,
        ], 'building_film'))->assertSessionHasNoErrors();

        $item = \App\Models\SalesOrderItem::first();
        $this->assertSame('meter', $item->unit);
        $this->assertSame(12.0, (float) $item->quantity);
        $this->assertNull($item->film_lines);
    }

    public function test_so_created_from_a_quotation_is_prefilled_with_its_package_items(): void
    {
        $this->seedCatalog();
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $this->actingAs($admin)->post(route('quotations.store'), array_merge($this->payload($branch, [
            'description' => 'Fullset', 'vehicle_label' => 'Toyota Avanza',
            'film_lines' => [['part_id' => 1, 'category_id' => 1, 'product_id' => 1]],
        ]), ['valid_until' => now()->addDays(14)->toDateString()]))->assertSessionHasNoErrors();
        $quotation = Quotation::first();

        $page = $this->actingAs($admin)->get(route('sales-orders.create', ['from_quotation' => $quotation->id]))->assertOk();
        $page->assertSee('film_lines', false)->assertSee('Toyota Avanza', false);
    }

    public function test_sales_order_form_renders_the_shared_package_ui(): void
    {
        $this->seedCatalog();
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);

        $this->actingAs($admin)->get(route('sales-orders.create'))
            ->assertOk()->assertSee('pack-card', false)->assertSee('Tambah bagian', false)->assertSee('desc-picker', false);
    }

    public function test_prices_and_discount_seeded_from_a_quotation_are_whole_rupiah_without_decimals(): void
    {
        $this->seedCatalog();
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $this->actingAs($admin)->post(route('quotations.store'), array_merge($this->payload($branch, [
            'description' => 'Fullset', 'unit_price' => 3800000,
            'film_lines' => [['part_id' => 1, 'category_id' => 1, 'product_id' => 1]],
        ]), ['valid_until' => now()->addDays(14)->toDateString(), 'discount_amount' => 300000]))->assertSessionHasNoErrors();
        $quotation = Quotation::first();

        $page = $this->actingAs($admin)->get(route('sales-orders.create', ['from_quotation' => $quotation->id]))->assertOk();
        $html = $page->getContent();

        $this->assertStringNotContainsString('3800000.00', $html);
        $this->assertStringNotContainsString('300000.00', $html);
        $this->assertStringContainsString('name="discount_amount"', $html);
        $this->assertMatchesRegularExpression('/name="discount_amount"[^>]*value="300000"/s', $html, 'diskon quotation ikut terbawa ke SO sebagai bilangan bulat');
        $this->assertStringContainsString('step="1"', $html);
    }
}
