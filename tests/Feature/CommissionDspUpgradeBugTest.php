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
        $invoice = Invoice::factory()->create(['customer_id' => $dspPanoCustomer->id]);
        $this->loadForCalculator($dspPanoCustomer, $invoice);

        $split = app(CommissionCalculator::class)->resolveInstallerSplit($dspPanoCustomer, $invoice);

        $this->assertSame('dsp_panoramic', $split->configKey);
        $this->assertSame(65000, $split->teknisiAmount);
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
