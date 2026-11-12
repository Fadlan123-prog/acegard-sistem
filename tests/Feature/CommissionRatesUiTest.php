<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\InvoiceBuilding;
use App\Models\CustomerBuilding;
use App\Models\User;
use App\Services\CommissionRates;
use App\Services\CommissionReleaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Tarif komisi per kategori bisa diatur dari Manajemen Produk; tanpa pengaturan dipakai nilai bawaan config. */
class CommissionRatesUiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return $this->userWithRole('admin', Branch::factory()->create());
    }

    private function carCategory(int $id = 3, string $name = 'BLACK MASTER'): int
    {
        DB::table('category_products')->insert(['id' => $id, 'name' => $name, 'duration' => 5, 'created_at' => now(), 'updated_at' => now()]);

        return $id;
    }

    public function test_without_saved_rates_the_config_defaults_apply_and_derived_panoramic_rules_hold(): void
    {
        $rates = app(CommissionRates::class);

        $this->assertSame(120000, $rates->marketingCar(1, 'fullset'));
        $this->assertSame(120000, $rates->marketingCar(1, 'full_panoramic'));
        $this->assertSame(140000, $rates->marketingCar(1, 'dsp_panoramic'));
        $this->assertSame(0, $rates->marketingCar(99, 'fullset'), 'kategori baru tanpa tarif = 0');
        $this->assertSame(40000, $rates->buildingTechnicianPerMeter(1));
        $this->assertSame(25000, $rates->buildingMarketingPerMeter(1));
    }

    public function test_saved_rates_override_config_and_panoramic_dsp_is_double(): void
    {
        $admin = $this->admin();
        $id = $this->carCategory(3);

        $this->actingAs($admin)->put(route('product-management.categories.commission', ['car', $id]), [
            'marketing_fullset' => '95.000', 'marketing_dsp' => '40000', 'marketing_skkb' => '0',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $rates = app(CommissionRates::class);
        $this->assertSame(95000, $rates->marketingCar(3, 'fullset'));
        $this->assertSame(95000, $rates->marketingCar(3, 'ekstra_panoramic'));
        $this->assertSame(80000, $rates->marketingCar(3, 'dsp_panoramic'), '2× D/S/P');
        $this->assertSame(0, $rates->marketingCar(3, 'skkb'), '0 yang disimpan tidak jatuh ke config');
    }

    public function test_a_brand_new_category_gets_commission_once_rates_are_set(): void
    {
        $admin = $this->admin();
        $branch = Branch::factory()->create();
        $tukang = Employee::factory()->create(['branch_id' => $branch->id, 'job_position' => 'Teknisi']);
        $marketing = Employee::factory()->create(['branch_id' => $branch->id, 'job_position' => 'Marketing']);
        $catId = $this->carCategory(8, 'SUNBLAST');
        DB::table('products')->insert(['id' => 8, 'category_id' => $catId, 'name' => 'SB20', 'description' => '20%', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('parts')->insert(['id' => 1, 'name' => 'WINDSHIELD', 'created_at' => now(), 'updated_at' => now()]);

        $customer = Customer::factory()->create(['branch_id' => $branch->id, 'tukang_id' => $tukang->id, 'marketing_id' => $marketing->id, 'install_type' => 'fullset', 'has_panoramic' => false, 'install_date' => now()->toDateString()]);
        DB::table('customer_product')->insert(['customer_id' => $customer->id, 'product_id' => 8, 'part_id' => 1, 'category_product_id' => $catId, 'created_at' => now(), 'updated_at' => now()]);
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id]);

        $release = fn () => app(CommissionReleaseService::class)->release($invoice->fresh(), User::factory()->create()->id);
        $amount = fn () => (int) DB::table('commissions')->where('invoice_id', $invoice->id)->where('role', 'marketing')->value('amount');

        $release();
        $this->assertSame(0, $amount(), 'tarif belum diatur = Rp 0');

        $this->actingAs($admin)->put(route('product-management.categories.commission', ['car', $catId]), [
            'marketing_fullset' => '70000', 'marketing_dsp' => '30000', 'marketing_skkb' => '50000',
        ])->assertSessionHasNoErrors();
        DB::table('commissions')->where('invoice_id', $invoice->id)->delete();
        $release();

        $this->assertSame(70000, $amount());
    }

    public function test_building_per_meter_rates_come_from_the_saved_values(): void
    {
        $admin = $this->admin();
        $branch = Branch::factory()->create();
        DB::table('category_product_building')->insert(['id' => 6, 'name' => 'CERAMIC', 'duration' => 5, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('product_building')->insert(['id' => 6, 'category_product_building_id' => 6, 'name' => 'C1', 'description' => '10%', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($admin)->put(route('product-management.categories.commission', ['building', 6]), [
            'technician_per_meter' => '12000', 'marketing_per_meter' => '8000',
        ])->assertSessionHasNoErrors();

        $tech = Employee::factory()->create(['branch_id' => $branch->id, 'job_position' => 'Teknisi']);
        $mkt = Employee::factory()->create(['branch_id' => $branch->id, 'job_position' => 'Marketing']);
        $customer = CustomerBuilding::factory()->create(['branch_id' => $branch->id, 'marketing_id' => $mkt->id]);
        DB::table('customer_building_product')->insert([
            'customer_building_id' => $customer->id, 'product_building_id' => 6, 'category_product_building_id' => 6, 'meters' => 10,
            'warantee_start' => now()->toDateString(), 'warantee_end' => now()->addYears(5)->toDateString(), 'warantee_duration' => 5, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $customer->technicianMeters()->create(['employee_id' => $tech->id, 'category_product_building_id' => 6, 'meters' => 10]);
        $invoice = InvoiceBuilding::create([
            'customer_building_id' => $customer->id, 'branch_id' => $branch->id, 'user_id' => User::factory()->create()->id, 'type' => 'new',
            'price' => 5000000, 'discount' => 0, 'down_payment' => 0, 'total_price' => 5000000, 'remaining_payment' => 5000000,
            'status' => 'unpaid', 'invoice_number' => 'IB-1', 'invoice_date' => now()->toDateString(),
        ]);

        app(\App\Services\BuildingCommissionService::class)->release($invoice);

        $rows = DB::table('commissions')->where('invoice_building_id', $invoice->id)->pluck('amount', 'role');
        $this->assertSame(120000, (int) $rows['installer'], '10 m × 12.000');
        $this->assertSame(80000, (int) $rows['marketing'], '10 m × 8.000');
    }

    public function test_validation_messages_are_plain_and_nothing_is_saved_on_error(): void
    {
        $admin = $this->admin();
        $id = $this->carCategory(3);

        $this->actingAs($admin)->put(route('product-management.categories.commission', ['car', $id]), [
            'marketing_fullset' => 'abc', 'marketing_dsp' => '', 'marketing_skkb' => '99999999999',
        ])->assertSessionHasErrors(['marketing_fullset', 'marketing_dsp', 'marketing_skkb']);

        $this->assertSame(0, DB::table('commission_rates')->count());
    }

    public function test_page_shows_rates_warns_when_missing_and_access_is_limited(): void
    {
        $admin = $this->admin();
        $this->carCategory(3, 'BLACK MASTER');
        $this->carCategory(9, 'KATEGORI BARU');

        $page = $this->actingAs($admin)->get(route('product-management.index'))->assertOk();
        $page->assertSee('Komisi marketing per mobil')->assertSee('Rp 70.000')->assertSee('Atur Tarif');
        $page->assertSee('Tarif komisi belum diatur', false);   // kategori baru

        foreach (['teknisi', 'finance', 'marketing'] as $role) {
            $this->actingAs($this->userWithRole($role, Branch::factory()->create()))
                ->put(route('product-management.categories.commission', ['car', 3]), ['marketing_fullset' => 1, 'marketing_dsp' => 1, 'marketing_skkb' => 1])->assertForbidden();
        }
        foreach (['owner', 'superadmin'] as $role) {
            $this->actingAs($this->userWithRole($role, Branch::factory()->create()))->get(route('product-management.index'))->assertOk();
        }
    }

    public function test_deleting_a_category_removes_its_saved_rates(): void
    {
        $admin = $this->admin();
        $id = $this->carCategory(9, 'SEMENTARA');
        $this->actingAs($admin)->put(route('product-management.categories.commission', ['car', $id]), ['marketing_fullset' => 1000, 'marketing_dsp' => 500, 'marketing_skkb' => 700]);
        $this->assertSame(3, DB::table('commission_rates')->count());

        $this->actingAs($admin)->delete(route('product-management.categories.destroy', ['car', $id]))->assertRedirect();

        $this->assertSame(0, DB::table('commission_rates')->count());
    }
}
