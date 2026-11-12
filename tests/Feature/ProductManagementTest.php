<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Manajemen katalog produk: tambah/ubah/hapus kategori & produk film mobil/gedung dan produk PPF. */
class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin()
    {
        return $this->userWithRole('admin', Branch::factory()->create());
    }

    private function cat(string $table, string $name = 'NOTCH UV 400'): int
    {
        return DB::table($table)->insertGetId(['name' => $name, 'duration' => 5, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_access_is_limited_to_superadmin_admin_and_owner(): void
    {
        $branch = Branch::factory()->create();
        foreach (['admin', 'owner', 'superadmin'] as $role) {
            $this->actingAs($this->userWithRole($role, $branch))->get(route('product-management.index'))->assertOk();
        }
        foreach (['teknisi', 'finance', 'marketing', 'designer'] as $role) {
            $this->actingAs($this->userWithRole($role, $branch))->get(route('product-management.index'))->assertForbidden();
        }
        $this->actingAs($this->admin())->get(route('product-management.index', ['tab' => 'bogus']))->assertOk();   // jatuh ke tab bawaan
        $this->actingAs($this->admin())->post('/product-management/truck/categories', ['name' => 'X', 'duration' => 1])->assertNotFound();
    }

    public function test_add_edit_and_delete_a_car_category_with_products(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('product-management.categories.store', 'car'), ['name' => 'SUNBLAST', 'duration' => 3])->assertRedirect();
        $id = DB::table('category_products')->where('name', 'SUNBLAST')->value('id');
        $this->assertNotNull($id);

        $this->actingAs($admin)->put(route('product-management.categories.update', ['car', $id]), ['name' => 'SUNBLAST PRO', 'duration' => 4])->assertRedirect();
        $this->assertSame(['SUNBLAST PRO', 4], [DB::table('category_products')->find($id)->name, (int) DB::table('category_products')->find($id)->duration]);

        $this->actingAs($admin)->post(route('product-management.products.store', ['car', $id]), ['name' => 'SB20', 'description' => '20%'])->assertRedirect();
        $pid = DB::table('products')->where('name', 'SB20')->value('id');
        $this->assertSame($id, (int) DB::table('products')->find($pid)->category_id);

        $this->actingAs($admin)->put(route('product-management.products.update', ['car', $pid]), ['name' => 'SB25', 'description' => '25%'])->assertRedirect();
        $this->assertSame('25%', DB::table('products')->find($pid)->description);

        // kategori yang masih punya produk tidak bisa dihapus
        $this->actingAs($admin)->delete(route('product-management.categories.destroy', ['car', $id]))->assertSessionHas('error');
        $this->assertNotNull(DB::table('category_products')->find($id));

        $this->actingAs($admin)->delete(route('product-management.products.destroy', ['car', $pid]))->assertRedirect();
        $this->assertNull(DB::table('products')->find($pid));
        $this->actingAs($admin)->delete(route('product-management.categories.destroy', ['car', $id]))->assertRedirect();
        $this->assertNull(DB::table('category_products')->find($id));
    }

    public function test_building_catalog_works_the_same_way(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('product-management.categories.store', 'building'), ['name' => 'NANO CLEAR', 'duration' => 7])->assertRedirect();
        $id = DB::table('category_product_building')->where('name', 'NANO CLEAR')->value('id');
        $this->actingAs($admin)->post(route('product-management.products.store', ['building', $id]), ['name' => 'NC90', 'description' => '90%'])->assertRedirect();
        $pid = DB::table('product_building')->where('name', 'NC90')->value('id');
        $this->assertSame($id, (int) DB::table('product_building')->find($pid)->category_product_building_id);

        $this->actingAs($admin)->delete(route('product-management.products.destroy', ['building', $pid]))->assertRedirect();
        $this->actingAs($admin)->delete(route('product-management.categories.destroy', ['building', $id]))->assertRedirect();
        $this->assertNull(DB::table('category_product_building')->find($id));
    }

    public function test_validation_rejects_duplicates_and_bad_values(): void
    {
        $admin = $this->admin();
        $id = $this->cat('category_products', 'NOTCH UV 400');
        DB::table('products')->insert(['category_id' => $id, 'name' => 'NV70', 'description' => '20%', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($admin)->post(route('product-management.categories.store', 'car'), ['name' => 'NOTCH UV 400', 'duration' => 5])->assertSessionHasErrors('name');
        $this->actingAs($admin)->post(route('product-management.categories.store', 'car'), ['name' => 'BARU', 'duration' => 99])->assertSessionHasErrors('duration');
        $this->actingAs($admin)->post(route('product-management.products.store', ['car', $id]), ['name' => 'NV70', 'description' => '30%'])->assertSessionHasErrors('name');
        $this->actingAs($admin)->post(route('product-management.products.store', ['car', $id]), ['name' => 'NV60'])->assertSessionHasErrors('description');

        // kode yang sama boleh di kategori lain
        $other = $this->cat('category_products', 'BLACK MASTER');
        $this->actingAs($admin)->post(route('product-management.products.store', ['car', $other]), ['name' => 'NV70', 'description' => '20%'])->assertSessionHasNoErrors();
        // mengubah dengan nama sendiri tidak dianggap duplikat
        $this->actingAs($admin)->put(route('product-management.categories.update', ['car', $id]), ['name' => 'NOTCH UV 400', 'duration' => 6])->assertSessionHasNoErrors();
    }

    public function test_used_category_and_product_cannot_be_deleted_and_the_message_explains_why(): void
    {
        $admin = $this->admin();
        $branch = Branch::factory()->create();
        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $catId = $this->cat('category_products');
        $prodId = DB::table('products')->insertGetId(['category_id' => $catId, 'name' => 'NV70', 'description' => '20%', 'created_at' => now(), 'updated_at' => now()]);
        $partId = DB::table('parts')->insertGetId(['name' => 'WINDSHIELD', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('customer_product')->insert(['customer_id' => $customer->id, 'product_id' => $prodId, 'part_id' => $partId, 'category_product_id' => $catId, 'created_at' => now(), 'updated_at' => now()]);

        // produk dipakai customer
        $r = $this->actingAs($admin)->delete(route('product-management.products.destroy', ['car', $prodId]));
        $this->assertStringContainsString('1 customer mobil', session('error'));
        $this->assertNotNull(DB::table('products')->find($prodId));

        // kategori punya produk -> ditolak; setelah produk dihapus paksa, kategori tetap ditolak karena dipakai customer
        $this->actingAs($admin)->delete(route('product-management.categories.destroy', ['car', $catId]))->assertSessionHas('error');
        $this->assertNotNull(DB::table('category_products')->find($catId));

        // riwayat customer utuh
        $this->assertSame(1, DB::table('customer_product')->where('customer_id', $customer->id)->count());
    }

    public function test_category_used_by_stock_or_sales_order_is_protected(): void
    {
        $admin = $this->admin();
        $branch = Branch::factory()->create();
        $catId = $this->cat('category_products');

        DB::table('film_stock_pools')->insert([
            'branch_id' => $branch->id, 'category_product_id' => $catId, 'film_type' => 'NOTCH', 'darkness_code' => 'NV70', 'usage_for' => 'car',
            'width_meter' => 1.5, 'total_received_length_meter' => 10, 'total_used_length_meter' => 0, 'remaining_length_meter' => 10,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($admin)->delete(route('product-management.categories.destroy', ['car', $catId]))->assertSessionHas('error');
        $this->assertStringContainsString('stok roll', session('error'));
        $this->assertNotNull(DB::table('category_products')->find($catId));
    }

    public function test_ppf_product_in_use_cannot_be_deleted_but_unused_can(): void
    {
        $admin = $this->admin();
        $branch = Branch::factory()->create();
        $used = DB::table('ppf_products')->insertGetId(['name' => 'Clear', 'brand' => 'A', 'thickness_microns' => 150, 'finish' => 'gloss', 'created_at' => now(), 'updated_at' => now()]);
        $free = DB::table('ppf_products')->insertGetId(['name' => 'Matte', 'brand' => 'A', 'thickness_microns' => 150, 'finish' => 'matte', 'created_at' => now(), 'updated_at' => now()]);
        $customer = \App\Models\CustomerPpf::factory()->create(['branch_id' => $branch->id]);
        \App\Models\CustomerPpfInstallation::create([
            'customer_ppf_id' => $customer->id, 'ppf_product_id' => $used, 'branch_id' => $branch->id, 'install_type' => 'fullbody',
            'install_date' => now()->toDateString(), 'warranty_code' => 'W-1', 'warranty_start' => now()->toDateString(), 'warranty_years' => 5, 'warranty_end' => now()->addYears(5)->toDateString(),
        ]);

        $this->actingAs($admin)->delete(route('ppf-products.destroy', $used))->assertSessionHas('error');
        $this->assertNotNull(DB::table('ppf_products')->find($used));

        $this->actingAs($admin)->delete(route('ppf-products.destroy', $free))->assertRedirect();
        $this->assertNull(DB::table('ppf_products')->find($free));

        $this->actingAs($admin)->get(route('product-management.index', ['tab' => 'ppf']))->assertOk()->assertSee('Clear')->assertSee('1 instalasi PPF');
    }

    public function test_pages_render_for_each_tab_with_data(): void
    {
        $admin = $this->admin();
        $catId = $this->cat('category_products', 'NOTCH UV 400');
        DB::table('products')->insert(['category_id' => $catId, 'name' => 'NV70', 'description' => '20%', 'created_at' => now(), 'updated_at' => now()]);
        $bId = $this->cat('category_product_building', 'FLEX ONE WAY');
        DB::table('product_building')->insert(['category_product_building_id' => $bId, 'name' => 'Silver Grey', 'description' => '80%', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($admin)->get(route('product-management.index'))->assertOk()->assertSee('NOTCH UV 400')->assertSee('NV70')->assertSee('Tambah Kategori');
        $this->actingAs($admin)->get(route('product-management.index', ['tab' => 'building']))->assertOk()->assertSee('FLEX ONE WAY')->assertSee('Silver Grey');
        $this->actingAs($admin)->get(route('product-management.index', ['tab' => 'ppf']))->assertOk()->assertSee('Tambah Produk PPF');
    }
}
