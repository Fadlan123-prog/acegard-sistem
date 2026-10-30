<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Services\CommissionReleaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Mobil ke-1 / ke-2 / ke-3 pada hari yang sama untuk satu tukang: komisi naik bertahap
 * (fullset -> fullset_ekstra -> fullset_ekstra_plus), baik bagian tukang maupun kenek.
 */
class CommissionSameDayTierTest extends TestCase
{
    use RefreshDatabase;

    private Employee $tukang;
    private Employee $kenek;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branch = Branch::factory()->create();
        $this->tukang = Employee::factory()->create(['branch_id' => $this->branch->id, 'job_position' => 'Teknisi']);
        $this->kenek  = Employee::factory()->create(['branch_id' => $this->branch->id, 'job_position' => 'Kenek']);
    }

    private function car(array $over = []): Invoice
    {
        $customer = Customer::factory()->create(array_merge([
            'branch_id' => $this->branch->id, 'tukang_id' => $this->tukang->id, 'kenek_id' => $this->kenek->id,
            'install_type' => 'fullset', 'has_panoramic' => false, 'install_date' => now()->toDateString(),
        ], $over));

        return Invoice::factory()->create(['customer_id' => $customer->id]);
    }

    private function release(Invoice $invoice): array
    {
        app(CommissionReleaseService::class)->release($invoice, \App\Models\User::factory()->create()->id);

        return DB::table('commissions')->where('invoice_id', $invoice->id)->where('role', 'installer')
            ->pluck('amount', 'employees_id')->map(fn ($a) => (int) $a)->all();
    }

    public function test_three_fullset_cars_same_day_step_up_for_tukang_and_kenek(): void
    {
        $i1 = $this->car();
        $i2 = $this->car();
        $i3 = $this->car();
        $i4 = $this->car();

        $r1 = $this->release($i1);
        $r2 = $this->release($i2);
        $r3 = $this->release($i3);
        $r4 = $this->release($i4);

        $this->assertSame([$this->tukang->id => 70000, $this->kenek->id => 30000], $r1);
        $this->assertSame([$this->tukang->id => 105000, $this->kenek->id => 45000], $r2);
        $this->assertSame([$this->tukang->id => 140000, $this->kenek->id => 60000], $r3);
        $this->assertSame([$this->tukang->id => 140000, $this->kenek->id => 60000], $r4, 'mobil ke-4 dst tetap tier plus');
    }

    public function test_tier_follows_entry_order_not_release_order(): void
    {
        $i1 = $this->car();
        $i2 = $this->car();
        $i3 = $this->car();

        $r3 = $this->release($i3);
        $r1 = $this->release($i1);
        $r2 = $this->release($i2);

        $this->assertSame(140000, $r3[$this->tukang->id]);
        $this->assertSame(70000, $r1[$this->tukang->id]);
        $this->assertSame(105000, $r2[$this->tukang->id]);
    }

    public function test_panoramic_cars_step_up_with_panoramic_bonus(): void
    {
        $i1 = $this->car(['has_panoramic' => true]);
        $i2 = $this->car(['has_panoramic' => true]);
        $i3 = $this->car(['has_panoramic' => true]);

        $this->assertSame([$this->tukang->id => 100000, $this->kenek->id => 45000], $this->release($i1));
        $this->assertSame([$this->tukang->id => 135000, $this->kenek->id => 60000], $this->release($i2));
        $this->assertSame([$this->tukang->id => 170000, $this->kenek->id => 75000], $this->release($i3));
    }

    public function test_cars_on_different_days_do_not_step_up(): void
    {
        $i1 = $this->car(['install_date' => now()->subDay()->toDateString()]);
        $i2 = $this->car(['install_date' => now()->toDateString()]);

        $this->assertSame(70000, $this->release($i1)[$this->tukang->id]);
        $this->assertSame(70000, $this->release($i2)[$this->tukang->id]);
    }

    public function test_solo_installer_steps_up(): void
    {
        $i1 = $this->car(['kenek_id' => null]);
        $i2 = $this->car(['kenek_id' => null]);
        $i3 = $this->car(['kenek_id' => null]);

        $this->assertSame(100000, $this->release($i1)[$this->tukang->id]);
        $this->assertSame(150000, $this->release($i2)[$this->tukang->id]);
        $this->assertSame(200000, $this->release($i3)[$this->tukang->id]);
    }

    /**
     * Mendokumentasikan aturan yang berlaku: tier ditentukan oleh jumlah mobil fullset
     * TUKANG pada hari itu; kenek mengikuti tier tukangnya. Kenek yang berpindah tukang
     * tiap mobil tidak naik tier (tiap mobil dihitung mobil ke-1 milik tukang barunya).
     */
    public function test_tier_is_counted_per_tukang_and_kenek_follows_the_tukang_tier(): void
    {
        $other = Employee::factory()->create(['branch_id' => $this->branch->id, 'job_position' => 'Teknisi']);

        $i1 = $this->car();
        $i2 = $this->car(['tukang_id' => $other->id]);

        $this->assertSame(30000, $this->release($i1)[$this->kenek->id]);
        $this->assertSame(30000, $this->release($i2)[$this->kenek->id]);
    }
}
