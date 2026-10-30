<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\User;
use App\Services\CommissionReleaseService;
use App\Services\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Komisi marketing: tarif per kategori produk (1 = 4K Alpha Pro, 2 = Notch UV 400) dan tipe pemasangan. */
class MarketingCommissionTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;
    private Employee $marketing;
    private Employee $tukang;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branch    = Branch::factory()->create();
        $this->marketing = Employee::factory()->create(['branch_id' => $this->branch->id, 'job_position' => 'Marketing']);
        $this->tukang    = Employee::factory()->create(['branch_id' => $this->branch->id, 'job_position' => 'Teknisi']);

        foreach ([1, 2] as $id) {
            DB::table('category_products')->insert(['id' => $id, 'name' => "C$id", 'duration' => 5, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('products')->insert(['id' => $id, 'category_id' => $id, 'name' => "P$id", 'created_at' => now(), 'updated_at' => now()]);
        }
        DB::table('parts')->insert(['id' => 1, 'name' => 'Depan', 'created_at' => now(), 'updated_at' => now()]);
    }

    private function invoice(array $customer = [], array $categories = [1]): Invoice
    {
        $c = Customer::factory()->create(array_merge([
            'branch_id' => $this->branch->id, 'tukang_id' => $this->tukang->id, 'kenek_id' => null,
            'marketing_id' => $this->marketing->id, 'install_type' => 'fullset', 'has_panoramic' => false,
            'install_date' => now()->toDateString(),
        ], $customer));

        foreach ($categories as $cat) {
            DB::table('customer_product')->insert([
                'customer_id' => $c->id, 'product_id' => $cat, 'part_id' => 1, 'category_product_id' => $cat,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return Invoice::factory()->create(['customer_id' => $c->id]);
    }

    private function marketingRows(Invoice $i)
    {
        return DB::table('commissions')->where('invoice_id', $i->id)->where('role', 'marketing')->get();
    }

    private function release(Invoice $i): void
    {
        app(CommissionReleaseService::class)->release($i, User::factory()->create()->id);
    }

    public function test_rates_per_category_and_install_type(): void
    {
        $cases = [
            // [category, install_type, panoramic, expected]
            [1, 'fullset', false, 120000], [1, 'fullset', true, 120000], [1, 'dsp', false, 70000],
            [1, 'dsp', true, 140000], [1, 'skkb', false, 100000],
            [2, 'fullset', false, 70000], [2, 'dsp', false, 30000], [2, 'dsp', true, 60000], [2, 'skkb', false, 50000],
        ];

        foreach ($cases as [$cat, $type, $pano, $expected]) {
            $i = $this->invoice(['install_type' => $type, 'has_panoramic' => $pano], [$cat]);
            $this->release($i);

            $rows = $this->marketingRows($i);
            $this->assertCount(1, $rows, "$type/$cat");
            $this->assertSame($expected, (int) $rows[0]->amount, "kategori $cat $type" . ($pano ? ' panoramic' : ''));
        }
    }

    public function test_marketing_is_not_affected_by_installer_same_day_tier(): void
    {
        $this->release($this->invoice());
        $second = $this->invoice();
        $this->release($second);
        $third = $this->invoice();
        $this->release($third);

        $this->assertSame(120000, (int) $this->marketingRows($second)[0]->amount);
        $this->assertSame(120000, (int) $this->marketingRows($third)[0]->amount);
    }

    public function test_priority_category_two_wins_when_both_categories_are_present(): void
    {
        $i = $this->invoice([], [1, 2]);
        $this->release($i);

        $this->assertSame(70000, (int) $this->marketingRows($i)[0]->amount);
    }

    public function test_release_twice_does_not_duplicate_marketing_row(): void
    {
        $i = $this->invoice();
        $this->release($i);

        // Rilis ulang lewat InvoiceController menghapus komisi invoice dulu, lalu rilis lagi.
        DB::table('commissions')->where('invoice_id', $i->id)->delete();
        $this->release($i->fresh());
        app(CommissionService::class)->createMarketingForCustomerAutoCategory(
            $this->marketing->id, $i->customer_id, $i->id, 'fullset', (int) $i->price
        );

        $this->assertCount(1, $this->marketingRows($i));
    }

    public function test_per_employee_commission_mode_overrides_default(): void
    {
        $this->marketing->update(['commission_mode' => 'none']);
        $none = $this->invoice();
        $this->release($none);
        $this->assertSame(0, (int) $this->marketingRows($none)[0]->amount);

        $this->marketing->update(['commission_mode' => 'percentage', 'commission_percentage' => 0.02]);
        $pct = $this->invoice();
        $this->release($pct);
        $this->assertSame((int) round($pct->price * 0.02), (int) $this->marketingRows($pct)[0]->amount);
    }

    public function test_reassigning_marketing_uses_the_customers_real_install_type(): void
    {
        $other = Employee::factory()->create(['branch_id' => $this->branch->id, 'job_position' => 'Marketing']);
        $i = $this->invoice(['install_type' => 'dsp'], [1]);
        $this->release($i);
        $this->assertSame(70000, (int) $this->marketingRows($i)[0]->amount);

        app(CommissionService::class)->reassignMarketingAfterRelease($i, $i->customer->fresh(), $other->id);

        $rows = $this->marketingRows($i);
        $this->assertCount(1, $rows);
        $this->assertSame($other->id, (int) $rows[0]->employees_id);
        $this->assertSame(70000, (int) $rows[0]->amount, 'dsp tetap 70rb, bukan tarif fullset 120rb');
    }

    public function test_reassigning_marketing_is_refused_once_commission_is_paid(): void
    {
        $other = Employee::factory()->create(['branch_id' => $this->branch->id, 'job_position' => 'Marketing']);
        $i = $this->invoice();
        $this->release($i);
        DB::table('commissions')->where('invoice_id', $i->id)->where('role', 'marketing')->update(['status' => 'paid']);

        $this->expectException(\DomainException::class);
        try {
            app(CommissionService::class)->reassignMarketingAfterRelease($i, $i->customer->fresh(), $other->id);
        } finally {
            $this->assertCount(1, $this->marketingRows($i));
            $this->assertSame($this->marketing->id, (int) $this->marketingRows($i)[0]->employees_id);
        }
    }
}
