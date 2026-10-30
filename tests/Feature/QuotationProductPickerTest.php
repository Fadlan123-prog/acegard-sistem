<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Services\QuotationCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QuotationProductPickerTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalog(): void
    {
        DB::table('category_products')->insert(['id' => 1, 'name' => 'NOTCH UV 400', 'duration' => 5, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('products')->insert(['id' => 1, 'category_id' => 1, 'name' => 'NV70', 'description' => '20%', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('category_product_building')->insert(['id' => 1, 'name' => 'FLEX ONE WAY', 'duration' => 5, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('product_building')->insert(['id' => 1, 'category_product_building_id' => 1, 'name' => 'Silver Grey', 'description' => '80%', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('ppf_products')->insert(['name' => 'Clear Gloss', 'brand' => 'Acegard', 'series' => 'X1', 'thickness_microns' => 150, 'finish' => 'gloss', 'default_warranty_years' => 7, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_catalog_lists_products_per_category_with_auto_specification(): void
    {
        $this->seedCatalog();
        $catalog = (new QuotationCatalog)->build();

        $car = $catalog['car_film'][0];
        $this->assertSame('NOTCH UV 400', $car['group']);
        $this->assertSame('NOTCH UV 400 — NV70', $car['items'][0]['label']);
        $this->assertSame('Kegelapan 20%, garansi 5 tahun', $car['items'][0]['spec']);
        $this->assertSame('FLEX ONE WAY — Silver Grey', $catalog['building_film'][0]['items'][0]['label']);
        $this->assertStringContainsString('150 micron', $catalog['ppf'][0]['items'][0]['spec']);
    }

    public function test_quotation_form_renders_the_picker_and_catalog_and_saves_the_chosen_product(): void
    {
        $this->seedCatalog();
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);

        $this->actingAs($admin)->get(route('quotations.create'))
            ->assertOk()->assertSee('desc-picker', false)->assertSee('NV70', false)->assertSee('Lainnya (ketik manual)', false);

        $this->actingAs($admin)->post(route('quotations.store'), [
            'category' => 'car_film', 'prospect_name' => 'Budi', 'prospect_phone' => '0812', 'date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(), 'discount_amount' => 0, 'branch_id' => $branch->id,
            'items' => [['description' => 'NOTCH UV 400 — NV70', 'specifications' => 'Kegelapan 20%, garansi 5 tahun',
                         'unit' => 'unit', 'quantity' => 1, 'unit_price' => 1500000, 'discount_percent' => 0]],
        ]);

        $this->assertDatabaseHas('quotation_items', ['description' => 'NOTCH UV 400 — NV70', 'specifications' => 'Kegelapan 20%, garansi 5 tahun']);
    }

    private function payload(Branch $branch, array $item, string $category = 'car_film'): array
    {
        return [
            'category' => $category, 'prospect_name' => 'Budi', 'prospect_phone' => '0812', 'date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(), 'discount_amount' => 0, 'branch_id' => $branch->id,
            'items' => [array_merge(['unit' => 'paket', 'quantity' => 1, 'unit_price' => 2500000, 'discount_percent' => 0], $item)],
        ];
    }

    private function seedParts(): void
    {
        DB::table('parts')->insert([
            ['id' => 1, 'name' => 'WINDSHIELD', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'FIRST ROW SIDE WINDOW', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'name' => 'SUNROOF/PANORAMIC', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function test_car_item_records_glass_parts_film_type_and_darkness_and_composes_text_on_the_server(): void
    {
        $this->seedCatalog();
        $this->seedParts();
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);

        $this->actingAs($admin)->post(route('quotations.store'), $this->payload($branch, [
            'part_ids' => [1, 6], 'film_category_id' => 1, 'film_product_id' => 1,
            'description' => 'teks palsu dari browser', 'specifications' => 'teks palsu',
        ]))->assertSessionHasNoErrors();

        $item = \App\Models\QuotationItem::first();
        $this->assertSame([1, 6], $item->part_ids);
        $this->assertSame(1, (int) $item->film_category_id);
        $this->assertSame(1, (int) $item->film_product_id);
        $this->assertSame('Kaca Depan, Sunroof / Panoramic', $item->description, 'disusun server, bukan dari browser');
        $this->assertSame('NOTCH UV 400 NV70 (kegelapan 20%)', $item->specifications);
    }

    public function test_darkness_must_belong_to_the_selected_film_type(): void
    {
        $this->seedCatalog();
        $this->seedParts();
        DB::table('category_products')->insert(['id' => 2, 'name' => 'BLACK MASTER', 'duration' => 5, 'created_at' => now(), 'updated_at' => now()]);
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);

        $this->actingAs($admin)->post(route('quotations.store'), $this->payload($branch, [
            'part_ids' => [1], 'film_category_id' => 2, 'film_product_id' => 1,   // NV70 bukan milik Black Master
        ]))->assertSessionHasErrors('items.0.film_product_id');

        $this->assertSame(0, \App\Models\QuotationItem::count());
    }

    public function test_non_car_quotations_ignore_glass_part_fields(): void
    {
        $this->seedCatalog();
        $this->seedParts();
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);

        $this->actingAs($admin)->post(route('quotations.store'), $this->payload($branch, [
            'description' => 'FLEX ONE WAY — Silver Grey', 'specifications' => 'Kegelapan 80%',
            'part_ids' => [1], 'film_category_id' => 1, 'film_product_id' => 1,
        ], 'building_film'))->assertSessionHasNoErrors();

        $item = \App\Models\QuotationItem::first();
        $this->assertSame('FLEX ONE WAY — Silver Grey', $item->description);
        $this->assertNull($item->part_ids);
        $this->assertNull($item->film_product_id);
    }

    public function test_editing_a_car_quotation_reloads_the_saved_selection(): void
    {
        $this->seedCatalog();
        $this->seedParts();
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $this->actingAs($admin)->post(route('quotations.store'), $this->payload($branch, ['part_ids' => [1, 2], 'film_category_id' => 1, 'film_product_id' => 1]));
        $quotation = \App\Models\Quotation::first();

        $page = $this->actingAs($admin)->get(route('quotations.edit', $quotation))->assertOk();
        $page->assertSee('part_ids', false)->assertSee('film_product_id', false)->assertSee('Samping Baris 1', false);
    }
}
