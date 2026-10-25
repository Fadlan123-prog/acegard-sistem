<?php

namespace Tests\Feature\Performance;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerBuilding;
use App\Models\CustomerPpf;
use App\Models\CustomerPpfInstallation;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression tests for the Phase 7 N+1 fixes. The pattern: render the same index page
 * against a small batch and a larger batch of rows, and assert the query count does NOT
 * grow with the row count. An N+1 bug means "queries scale with rows"; the fix means
 * "queries stay flat because everything needed was eager-loaded up front".
 */
class EagerLoadingTest extends TestCase
{
    use RefreshDatabase;

    private function countQueries(\Closure $callback): int
    {
        $count = 0;
        DB::listen(function () use (&$count) { $count++; });
        $callback();
        return $count;
    }

    public function test_customer_index_query_count_does_not_scale_with_row_count(): void
    {
        $branch = Branch::factory()->create();
        $staff  = $this->userWithRole('admin', $branch);

        $makeCustomersWithInvoices = function (int $n) use ($branch) {
            Customer::query()->delete();
            Customer::factory($n)->create(['branch_id' => $branch->id])
                ->each(fn ($c) => Invoice::factory()->create(['customer_id' => $c->id]));
        };

        $makeCustomersWithInvoices(2);
        $small = $this->countQueries(fn () => $this->actingAs($staff)->get(route('customer.index'))->assertSuccessful());

        $makeCustomersWithInvoices(10);
        $large = $this->countQueries(fn () => $this->actingAs($staff)->get(route('customer.index'))->assertSuccessful());

        $this->assertLessThanOrEqual(
            $small + 2, // small tolerance for pagination count queries etc.
            $large,
            "Query count grew from {$small} (2 rows) to {$large} (10 rows) — customer.index is N+1 again."
        );
    }

    public function test_customer_building_index_query_count_does_not_scale_with_row_count(): void
    {
        $branch = Branch::factory()->create();
        $staff  = $this->userWithRole('admin', $branch);

        $make = function (int $n) use ($branch, $staff) {
            CustomerBuilding::query()->delete();
            CustomerBuilding::factory($n)->create(['branch_id' => $branch->id])
                ->each(fn ($c) => DB::table('invoice_building')->insert([
                    'customer_building_id' => $c->id,
                    'branch_id'            => $branch->id,
                    'user_id'              => $staff->id,
                    'type'                 => 'car film',
                    'price'                => 500000,
                    'discount'             => 0,
                    'down_payment'         => 0,
                    'total_price'          => 500000,
                    'remaining_payment'    => 500000,
                    'status'               => 'unpaid',
                    'invoice_number'       => 'INVB-' . $c->id,
                    'invoice_date'         => now(),
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]));
        };

        $make(2);
        $small = $this->countQueries(fn () => $this->actingAs($staff)->get(route('customer.building.index'))->assertSuccessful());

        $make(10);
        $large = $this->countQueries(fn () => $this->actingAs($staff)->get(route('customer.building.index'))->assertSuccessful());

        $this->assertLessThanOrEqual($small + 2, $large, "Query count grew from {$small} to {$large} — customer.building.index is N+1 again.");
    }

    public function test_ppf_customer_index_query_count_does_not_scale_with_row_count(): void
    {
        $branch = Branch::factory()->create();
        $staff  = $this->userWithRole('admin', $branch);

        $ppfProduct = \App\Models\PpfProduct::create([
            'name' => 'Test PPF', 'brand' => 'Test', 'thickness_microns' => 150, 'finish' => 'gloss',
        ]);

        $make = function (int $n) use ($branch, $ppfProduct) {
            CustomerPpf::query()->delete();
            CustomerPpf::factory($n)->create(['branch_id' => $branch->id])->each(function ($c) use ($branch, $ppfProduct) {
                $installation = CustomerPpfInstallation::create([
                    'customer_ppf_id' => $c->id,
                    'branch_id'       => $branch->id,
                    'ppf_product_id'  => $ppfProduct->id,
                    'install_type'    => 'fullbody',
                    'install_date'    => now()->toDateString(),
                    'warranty_code'   => 'W-' . $c->id,
                    'warranty_start'  => now()->toDateString(),
                    'warranty_years'  => 5,
                    'warranty_end'    => now()->addYears(5)->toDateString(),
                    'warranty_status' => 'active',
                    'panels'          => [],
                ]);
                DB::table('ppf_invoices')->insert([
                    'branch_id'                     => $branch->id,
                    'customer_ppf_id'                => $c->id,
                    'customer_ppf_installation_id'    => $installation->id,
                    'invoice_number'                 => 'PPFI-' . $c->id,
                    'invoice_date'                    => now()->toDateString(),
                    'created_at'                       => now(),
                    'updated_at'                        => now(),
                ]);
            });
        };

        $make(2);
        $small = $this->countQueries(fn () => $this->actingAs($staff)->get(route('ppf-customers.index'))->assertSuccessful());

        $make(10);
        $large = $this->countQueries(fn () => $this->actingAs($staff)->get(route('ppf-customers.index'))->assertSuccessful());

        $this->assertLessThanOrEqual($small + 2, $large, "Query count grew from {$small} to {$large} — ppf-customers.index is N+1 again.");
    }

    public function test_branch_index_no_longer_loads_full_unbounded_related_collections(): void
    {
        $admin = $this->superadmin();

        $branch = Branch::factory()->create();
        Customer::factory(25)->create(['branch_id' => $branch->id]);
        CustomerBuilding::factory(25)->create(['branch_id' => $branch->id]);

        $queryCount = $this->countQueries(function () use ($admin) {
            $this->actingAs($admin)->get(route('branches.index'))->assertSuccessful();
        });

        // Regardless of how many customers/buildings exist per branch, the index page
        // must only ever run a small, fixed number of queries (list + counts) — never
        // one that scales with each branch's actual customer/building/employee totals.
        $this->assertLessThan(20, $queryCount, "branches.index ran {$queryCount} queries for a single branch with 50 related rows — the unbounded modal load is back.");
    }
}
