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

    private function seedAllParts(): void
    {
        DB::table('parts')->whereIn('id', [1, 2])->delete();
        $names = [1 => 'WINDSHIELD', 2 => 'FIRST ROW SIDE WINDOW', 3 => 'SECOND ROW SIDE WINDOW', 4 => 'THIRD ROW SIDE WINDOW', 5 => 'REAR WINDOW', 6 => 'SUNROOF/PANORAMIC'];
        foreach ($names as $id => $n) {
            DB::table('parts')->insert(['id' => $id, 'name' => $n, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    private function carSo(Branch $branch, $admin): SalesOrder
    {
        $this->actingAs($admin)->post(route('sales-orders.store'), $this->payload($branch, [
            'description' => 'Fullset', 'vehicle_label' => 'Toyota Avanza', 'vehicle_plate' => 'B 1234 XY',
            'film_lines' => [
                ['part_id' => 6, 'category_id' => 1, 'product_id' => 1],   // sengaja tidak berurutan
                ['part_id' => 1, 'category_id' => 1, 'product_id' => 1],
                ['part_id' => 5, 'category_id' => 2, 'product_id' => 2],
            ],
        ]))->assertSessionHasNoErrors();

        return SalesOrder::latest('id')->first();
    }

    private function seedBlackMaster(): void
    {
        DB::table('category_products')->insert(['id' => 2, 'name' => 'BLACK MASTER', 'duration' => 5, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('products')->insert(['id' => 2, 'category_id' => 2, 'name' => 'BM35', 'description' => '40%', 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_film_breakdown_lists_every_glass_part_from_windshield_to_panoramic(): void
    {
        $this->seedCatalog();
        $this->seedAllParts();
        $this->seedBlackMaster();
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $so = $this->carSo($branch, $admin);

        $breakdown = $so->fresh()->filmBreakdown();

        $this->assertCount(1, $breakdown);
        $this->assertSame('Toyota Avanza · B 1234 XY', $breakdown[0]['vehicle']);
        $this->assertSame(
            ['Kaca Depan', 'Samping Baris 1', 'Samping Baris 2', 'Samping Baris 3', 'Kaca Belakang', 'Sunroof / Panoramic'],
            array_column($breakdown[0]['parts'], 'label'),
            'urut dari kaca depan sampai panoramic'
        );
        $films = array_column($breakdown[0]['parts'], 'film');
        $this->assertSame('NOTCH UV 400 NV70 (kegelapan 20%)', $films[0]);
        $this->assertNull($films[1]);
        $this->assertNull($films[3]);
        $this->assertSame('BLACK MASTER BM35 (kegelapan 40%)', $films[4]);
        $this->assertSame('NOTCH UV 400 NV70 (kegelapan 20%)', $films[5]);
    }

    public function test_assign_modal_and_documents_use_the_per_part_detail_from_the_so(): void
    {
        $this->seedCatalog();
        $this->seedAllParts();
        $this->seedBlackMaster();
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $so = $this->carSo($branch, $admin);
        $so->update(['status' => 'confirmed']);

        $show = $this->actingAs($admin)->get(route('sales-orders.show', $so))->assertOk();
        $show->assertSee('Diambil dari rincian harga SO', false)->assertSee('Sunroof / Panoramic')->assertSee('BLACK MASTER BM35 (kegelapan 40%)');
        $show->assertDontSee('id="filmCategory"', false);   // pilihan tunggal lama tidak dipakai lagi

        $this->actingAs($admin)->post(route('sales-orders.issue-spk', $so));
        $this->actingAs($admin)->get(route('sales-orders.spk', $so))->assertSee('Tipe Film / Kegelapan per Bagian Kaca')->assertSee('Kaca Belakang');
        $this->get(route('spk.public', $so->fresh()->spk_token))->assertSee('Samping Baris 3')->assertSee('BLACK MASTER BM35 (kegelapan 40%)');
    }

    public function test_assign_does_not_overwrite_when_the_so_has_per_part_detail(): void
    {
        $this->seedCatalog();
        $this->seedAllParts();
        $this->seedBlackMaster();
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $so = $this->carSo($branch, $admin);
        $so->update(['status' => 'confirmed']);

        $this->actingAs($admin)->post(route('sales-orders.assign', $so), ['film_category_id' => 1, 'film_product_id' => 1]);

        $this->assertNull($so->fresh()->film_category_id);
    }

    public function test_so_without_package_items_keeps_the_single_film_selection(): void
    {
        $this->seedCatalog();
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $this->actingAs($admin)->post(route('sales-orders.store'), $this->payload($branch, ['description' => 'Jasa kaca film']));
        $so = SalesOrder::first();
        $so->update(['status' => 'confirmed']);

        $this->assertSame([], $so->filmBreakdown());
        $this->actingAs($admin)->get(route('sales-orders.show', $so))->assertSee('id="filmCategory"', false);
    }
}
