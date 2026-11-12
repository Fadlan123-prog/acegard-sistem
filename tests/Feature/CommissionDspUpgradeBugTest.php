<?php

namespace Tests\Feature;

use App\Domain\Commission\CommissionCalculator;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression test: CommissionCalculator::resolveInstallerSplit() used to apply the
 * "2nd/3rd car of the day" fullset upgrade tier to ANY job type as long as the same
 * installer already had an earlier fullset job that day - including DSP and
 * DSP+Panoramic, which have their own flat rates and no upgrade tier at all. A DSP
 * job entered after a same-day fullset job would silently be priced as
 * fullset_ekstra / fullset_ekstra_plus instead of dsp / dsp_panoramic (2-4x over).
 */
class CommissionDspUpgradeBugTest extends TestCase
{
    use RefreshDatabase;

    private function makeInstallerPair(): array
    {
        $tukang = Employee::factory()->create(['job_position' => 'Teknisi']);
        $kenek  = Employee::factory()->create(['job_position' => 'Kenek']);

        return [$tukang, $kenek];
    }

    private function attachParts(Customer $customer, array $partNames): void
    {
        foreach ($partNames as $name) {
            $partId = \Illuminate\Support\Facades\DB::table('parts')->where('name', $name)->value('id')
                ?? \Illuminate\Support\Facades\DB::table('parts')->insertGetId(['name' => $name, 'created_at' => now(), 'updated_at' => now()]);
            $catId = \Illuminate\Support\Facades\DB::table('category_products')->value('id')
                ?? \Illuminate\Support\Facades\DB::table('category_products')->insertGetId(['name' => 'C', 'duration' => 5, 'created_at' => now(), 'updated_at' => now()]);
            $prodId = \Illuminate\Support\Facades\DB::table('products')->value('id')
                ?? \Illuminate\Support\Facades\DB::table('products')->insertGetId(['category_id' => $catId, 'name' => 'P', 'created_at' => now(), 'updated_at' => now()]);

            \Illuminate\Support\Facades\DB::table('customer_product')->insert([
                'customer_id' => $customer->id, 'product_id' => $prodId, 'part_id' => $partId,
                'category_product_id' => $catId, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    /** @return array{0:string,1:int,2:int} configKey, teknisi, kenek */
    private function dspSplit(array $parts, bool $panoramic): array
    {
        [$tukang, $kenek] = $this->makeInstallerPair();
        $customer = Customer::factory()->create([
            'tukang_id' => $tukang->id, 'kenek_id' => $kenek->id, 'install_type' => 'dsp',
            'has_panoramic' => $panoramic, 'install_date' => now()->toDateString(),
        ]);
        $this->attachParts($customer, $parts);
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id]);
        $this->loadForCalculator($customer, $invoice);
        $split = app(CommissionCalculator::class)->resolveInstallerSplit($customer, $invoice);

        return [$split->configKey, $split->teknisiAmount, $split->kenekAmount];
    }

    public function test_dsp_counts_double_only_for_windshield_plus_panoramic(): void
    {
        $this->assertSame(['dsp_panoramic', 70000, 30000], $this->dspSplit(['WINDSHIELD', 'SUNROOF/PANORAMIC'], true));
    }

    public function test_dsp_with_only_windshield_or_only_panoramic_is_a_single_dsp(): void
    {
        $this->assertSame(['dsp', 35000, 15000], $this->dspSplit(['WINDSHIELD'], false));
        $this->assertSame(['dsp', 35000, 15000], $this->dspSplit(['SUNROOF/PANORAMIC'], true), 'panoramic saja = 1 DSP');
        $this->assertSame(['dsp', 35000, 15000], $this->dspSplit(['WINDSHIELD'], true), 'flag panoramic tanpa bagian panoramic = 1 DSP');
    }

    public function test_dsp_with_extra_parts_beyond_windshield_and_panoramic_is_a_single_dsp(): void
    {
        $this->assertSame(['dsp', 35000, 15000], $this->dspSplit(['WINDSHIELD', 'SUNROOF/PANORAMIC', 'REAR WINDOW'], true));
    }

    public function test_windshield_plus_panoramic_without_the_panoramic_flag_stays_single(): void
    {
        $this->assertSame(['dsp', 35000, 15000], $this->dspSplit(['WINDSHIELD', 'SUNROOF/PANORAMIC'], false));
    }

    private function loadForCalculator(Customer $customer, Invoice $invoice): void
    {
        $customer->load(['tukang', 'kenek', 'marketing', 'customerProducts.part']);
        $invoice->load('customer');
    }

    public function test_dsp_job_after_a_same_day_fullset_job_still_uses_dsp_rate(): void
    {
        [$tukang, $kenek] = $this->makeInstallerPair();

        // Installer's first job today: fullset (lower id -> counted as "prior" by carCountForInstaller).
        Customer::factory()->create([
            'tukang_id'     => $tukang->id,
            'kenek_id'      => $kenek->id,
            'install_type'  => 'fullset',
            'has_panoramic' => false,
            'install_date'  => now()->toDateString(),
        ]);

        // Installer's second job today: DSP - must stay at the plain DSP rate.
        $dspCustomer = Customer::factory()->create([
            'tukang_id'     => $tukang->id,
            'kenek_id'      => $kenek->id,
            'install_type'  => 'dsp',
            'has_panoramic' => false,
            'install_date'  => now()->toDateString(),
        ]);
        $invoice = Invoice::factory()->create(['customer_id' => $dspCustomer->id]);
        $this->loadForCalculator($dspCustomer, $invoice);

        $split = app(CommissionCalculator::class)->resolveInstallerSplit($dspCustomer, $invoice);

        $this->assertSame('dsp', $split->configKey);
        $this->assertSame(35000, $split->teknisiAmount);
        $this->assertSame(15000, $split->kenekAmount);
        $this->assertFalse($split->isSecondCar);
    }

    public function test_dsp_panoramic_job_after_a_same_day_fullset_job_still_uses_dsp_panoramic_rate(): void
    {
        [$tukang, $kenek] = $this->makeInstallerPair();

        Customer::factory()->create([
            'tukang_id'     => $tukang->id,
            'kenek_id'      => $kenek->id,
            'install_type'  => 'fullset',
            'has_panoramic' => false,
            'install_date'  => now()->toDateString(),
        ]);

        $dspPanoCustomer = Customer::factory()->create([
            'tukang_id'     => $tukang->id,
            'kenek_id'      => $kenek->id,
            'install_type'  => 'dsp',
            'has_panoramic' => true,
            'install_date'  => now()->toDateString(),
        ]);
        $this->attachParts($dspPanoCustomer, ['WINDSHIELD', 'SUNROOF/PANORAMIC']);
        $invoice = Invoice::factory()->create(['customer_id' => $dspPanoCustomer->id]);
        $this->loadForCalculator($dspPanoCustomer, $invoice);

        $split = app(CommissionCalculator::class)->resolveInstallerSplit($dspPanoCustomer, $invoice);

        $this->assertSame('dsp_panoramic', $split->configKey);
        $this->assertSame(70000, $split->teknisiAmount);
        $this->assertSame(30000, $split->kenekAmount);
        $this->assertFalse($split->isSecondCar);
    }

    public function test_fullset_upgrade_tier_still_applies_to_fullset_jobs(): void
    {
        [$tukang, $kenek] = $this->makeInstallerPair();

        Customer::factory()->create([
            'tukang_id'     => $tukang->id,
            'kenek_id'      => $kenek->id,
            'install_type'  => 'fullset',
            'has_panoramic' => false,
            'install_date'  => now()->toDateString(),
        ]);

        $secondFullset = Customer::factory()->create([
            'tukang_id'     => $tukang->id,
            'kenek_id'      => $kenek->id,
            'install_type'  => 'fullset',
            'has_panoramic' => false,
            'install_date'  => now()->toDateString(),
        ]);
        $invoice = Invoice::factory()->create(['customer_id' => $secondFullset->id]);
        $this->loadForCalculator($secondFullset, $invoice);

        $split = app(CommissionCalculator::class)->resolveInstallerSplit($secondFullset, $invoice);

        $this->assertSame('fullset_ekstra', $split->configKey);
        $this->assertTrue($split->isSecondCar);
    }
}
