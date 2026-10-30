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
}
