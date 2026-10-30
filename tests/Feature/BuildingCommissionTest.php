<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CustomerBuilding;
use App\Models\Employee;
use App\Models\InvoiceBuilding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Komisi teknisi gedung: per meter menurut kategori film, tanpa kenek, dibagi rata, eligible saat lunas. */
class BuildingCommissionTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branch = Branch::factory()->create();

        foreach ([1, 2, 3, 4] as $id) {
            DB::table('category_product_building')->insert(['id' => $id, 'name' => "C$id", 'duration' => 5, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('product_building')->insert(['id' => $id, 'category_product_building_id' => $id, 'name' => "P$id", 'description' => '20%', 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    /**
     * @param array<int,float> $meters  kategori => total meter produk
     * @param array<int,array> $alloc   [indeks teknisi, kategori, meter]
     */
    private function job(array $meters, array $alloc, int $technicians = 2): array
    {
        $customer = CustomerBuilding::factory()->create(['branch_id' => $this->branch->id]);

        foreach ($meters as $cat => $m) {
            DB::table('customer_building_product')->insert([
                'customer_building_id' => $customer->id, 'product_building_id' => $cat,
                'category_product_building_id' => $cat, 'meters' => $m,
                'warantee_start' => now()->toDateString(), 'warantee_end' => now()->addYears(5)->toDateString(),
                'warantee_duration' => 5, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $techs = [];
        for ($i = 0; $i < $technicians; $i++) {
            $techs[] = Employee::factory()->create(['branch_id' => $this->branch->id, 'job_position' => 'Teknisi']);
        }
        foreach ($alloc as [$ti, $cat, $m]) {
            $customer->technicianMeters()->create([
                'employee_id' => $techs[$ti]->id, 'category_product_building_id' => $cat, 'meters' => $m,
            ]);
        }

        $user = User::factory()->create();
        $invoice = InvoiceBuilding::create([
            'customer_building_id' => $customer->id, 'branch_id' => $this->branch->id, 'user_id' => $user->id,
            'type' => 'new', 'price' => 5000000, 'discount' => 0, 'down_payment' => 0, 'total_price' => 5000000,
            'remaining_payment' => 5000000, 'status' => 'unpaid', 'invoice_number' => 'IB-' . uniqid(), 'invoice_date' => now()->toDateString(),
        ]);

        return [$invoice, $techs, $customer];
    }

    private function rows(InvoiceBuilding $i)
    {
        return DB::table('commissions')->where('invoice_building_id', $i->id)->pluck('amount', 'employees_id')->map(fn ($a) => (int) $a);
    }

    private function release(InvoiceBuilding $i): void
    {
        app(\App\Services\BuildingCommissionService::class)->release($i);
    }

    public function test_commission_follows_each_technicians_own_meters_not_an_equal_split(): void
    {
        // Pekerjaan 20 m 4K: A mengerjakan 15 m, B hanya 5 m (tidak dibagi rata).
        [$invoice, $t] = $this->job([1 => 20], [[0, 1, 15], [1, 1, 5]]);

        $this->release($invoice);

        $this->assertEquals([$t[0]->id => 600000, $t[1]->id => 200000], $this->rows($invoice)->all());
    }

    public function test_technician_working_several_categories_is_summed(): void
    {
        [$invoice, $t] = $this->job([1 => 10, 2 => 5, 3 => 4, 4 => 2], [[0, 1, 10], [0, 2, 5], [0, 3, 4], [0, 4, 2]], 1);

        $this->release($invoice);

        $this->assertSame(400000 + 150000 + 100000 + 30000, $this->rows($invoice)[$t[0]->id]);
    }

    public function test_technician_who_did_not_work_gets_nothing_and_unallocated_meters_are_unpaid(): void
    {
        [$invoice, $t] = $this->job([1 => 20], [[0, 1, 8]]); // 12 m belum dialokasikan

        $this->release($invoice);

        $this->assertEquals([$t[0]->id => 320000], $this->rows($invoice)->all());
    }

    public function test_no_allocation_means_no_commission_and_release_is_idempotent(): void
    {
        [$none] = $this->job([1 => 2], [], 1);
        $this->assertSame([], app(\App\Services\BuildingCommissionService::class)->release($none));

        [$invoice] = $this->job([1 => 2], [[0, 1, 2]], 1);
        $this->release($invoice);
        $this->release($invoice->fresh());

        $this->assertCount(1, $this->rows($invoice));
    }

    public function test_commission_is_created_when_the_invoice_is_paid_off(): void
    {
        [$invoice, $t] = $this->job([1 => 10], [[0, 1, 10]], 1);
        DB::table('film_stock_pools')->insert([
            'branch_id' => $this->branch->id, 'category_product_building_id' => 1, 'product_building_id' => 1,
            'film_type' => 'C1', 'darkness_code' => 'P1', 'usage_for' => 'building', 'width_meter' => 1.5,
            'total_received_length_meter' => 500, 'total_used_length_meter' => 0, 'remaining_length_meter' => 500,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $admin = $this->userWithRole('admin', $this->branch);

        $this->actingAs($admin)->post(route('invoice.building.pelunasan', $invoice->id))->assertRedirect();

        $this->assertNotNull($invoice->fresh()->pelunasan_at);
        $this->assertSame(400000, $this->rows($invoice)[$t[0]->id]);
        $this->assertSame('eligible', DB::table('commissions')->where('invoice_building_id', $invoice->id)->value('status'));
    }

    public function test_employee_with_no_commission_mode_gets_zero_but_row_is_kept(): void
    {
        [$invoice, $t] = $this->job([1 => 10], [[0, 1, 10]], 1);
        $t[0]->update(['commission_mode' => 'none']);

        $this->release($invoice);

        $this->assertSame(0, $this->rows($invoice)[$t[0]->id]);
    }

    public function test_admin_can_monitor_meters_per_technician(): void
    {
        [$invoice, $t] = $this->job([1 => 20], [[0, 1, 15], [1, 1, 5]]);
        $invoice->update(['pelunasan_at' => now(), 'status' => 'paid']);
        $admin = $this->userWithRole('admin', $this->branch);

        $report = $this->actingAs($admin)->get(route('commission.building-meters'))->assertOk()->viewData('report');

        $byName = $report->keyBy('employee');
        $this->assertSame(15.0, (float) $byName[$t[0]->name]['meters']);
        $this->assertSame(5.0, (float) $byName[$t[1]->name]['meters']);
        $this->assertSame(600000, (int) $byName[$t[0]->name]['amount']);
    }

    public function test_report_hides_other_branches(): void
    {
        [$invoice] = $this->job([1 => 20], [[0, 1, 15]], 1);
        $invoice->update(['pelunasan_at' => now(), 'status' => 'paid']);

        $other = Branch::factory()->create();
        $outsider = $this->userWithRole('admin', $other);

        $this->assertCount(0, $this->actingAs($outsider)->get(route('commission.building-meters'))->assertOk()->viewData('report'));
    }

    private function updatePayload(CustomerBuilding $c, array $techRows, float $meters = 20): array
    {
        return [
            'wsn' => $c->wsn, 'card_number' => $c->card_number, 'name' => $c->name, 'phone_number' => '0812', 'email' => 'a@b.test', 'address' => 'x', 'dealer_name' => 'x', 'applicator' => 'x', 'city' => 'x', 'country' => 'ID',
            'admin_password' => 'password',
            'products' => [['category_product_building_id' => 1, 'product_building_id' => 1, 'meters' => $meters, 'warantee_duration' => 5]],
            'technician_meters' => $techRows,
        ];
    }

    public function test_admin_cannot_allocate_more_meters_than_the_job_has(): void
    {
        [, $t, $customer] = $this->job([1 => 20], []);
        $admin = $this->userWithRole('admin', $this->branch);
        $admin->update(['password' => \Illuminate\Support\Facades\Hash::make('password')]);

        $this->actingAs($admin)->put(route('customer.building.update', $customer->id), $this->updatePayload($customer, [
            ['employee_id' => $t[0]->id, 'category_product_building_id' => 1, 'meters' => 15],
            ['employee_id' => $t[1]->id, 'category_product_building_id' => 1, 'meters' => 10],
        ]));

        $this->assertSame(0, $customer->technicianMeters()->count());

        $this->actingAs($admin)->put(route('customer.building.update', $customer->id), $this->updatePayload($customer, [
            ['employee_id' => $t[0]->id, 'category_product_building_id' => 1, 'meters' => 15],
            ['employee_id' => $t[1]->id, 'category_product_building_id' => 1, 'meters' => 5],
        ]));

        $this->assertSame(2, $customer->technicianMeters()->count());
    }
}
