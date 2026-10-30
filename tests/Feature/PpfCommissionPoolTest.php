<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CustomerPpf;
use App\Models\CustomerPpfInstallation;
use App\Models\Employee;
use App\Models\PpfInvoice;
use App\Services\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Komisi PPF: pool 11% dari total_price, semua teknisi sama rata, detailer +1% bila teknisi < 3. */
class PpfCommissionPoolTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branch = Branch::factory()->create();
    }

    /** @return array{0:PpfInvoice,1:array<int,Employee>,2:array<int,Employee>} */
    private function job(int $teknisi, int $detailer, int $total = 10000000, int $price = null): array
    {
        $customer = CustomerPpf::factory()->create(['branch_id' => $this->branch->id]);
        $productId = DB::table('ppf_products')->insertGetId([
            'name' => 'P', 'brand' => 'B', 'thickness_microns' => 150, 'finish' => 'gloss', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $installation = CustomerPpfInstallation::create([
            'customer_ppf_id' => $customer->id, 'ppf_product_id' => $productId, 'branch_id' => $this->branch->id,
            'install_type' => 'fullbody', 'install_date' => now()->toDateString(), 'warranty_code' => 'W-' . uniqid(),
            'warranty_start' => now()->toDateString(), 'warranty_years' => 5, 'warranty_end' => now()->addYears(5)->toDateString(),
        ]);

        $t = $d = [];
        for ($i = 0; $i < $teknisi; $i++) {
            $t[] = Employee::factory()->create(['branch_id' => $this->branch->id, 'job_position' => 'Teknisi PPF']);
        }
        for ($i = 0; $i < $detailer; $i++) {
            $d[] = Employee::factory()->create(['branch_id' => $this->branch->id, 'job_position' => 'Detailer']);
        }
        // sengaja diattach dengan urutan acak: hasil tidak boleh bergantung urutan pivot
        $all = collect(array_merge($t, $d))->shuffle();
        $installation->technicians()->sync($all->pluck('id')->all());

        $invoice = PpfInvoice::create([
            'branch_id' => $this->branch->id, 'customer_ppf_id' => $customer->id, 'customer_ppf_installation_id' => $installation->id,
            'invoice_number' => 'PPF-' . uniqid(), 'invoice_date' => now()->toDateString(), 'payment_method' => 'cash',
            'price' => $price ?? $total, 'discount' => ($price ?? $total) - $total, 'downpayment' => $total, 'total_price' => $total,
            'remaining_payment' => 0, 'status' => 'paid',
        ]);

        return [$invoice, $t, $d];
    }

    private function amounts(PpfInvoice $invoice): array
    {
        return DB::table('commissions')->where('ppf_invoice_id', $invoice->id)->whereIn('role', ['teknisi', 'detailer'])
            ->pluck('amount', 'employees_id')->map(fn ($a) => (int) $a)->all();
    }

    private function release(PpfInvoice $invoice): void
    {
        app(CommissionService::class)->createPpfCommissions($invoice);
    }

    public function test_full_team_three_technicians_and_one_detailer_totals_eleven_percent(): void
    {
        [$invoice, $t, $d] = $this->job(3, 1);
        $this->release($invoice);
        $a = $this->amounts($invoice);

        foreach ($t as $tech) {
            $this->assertSame(300000, $a[$tech->id], 'teknisi 3%');
        }
        $this->assertSame(200000, $a[$d[0]->id], 'detailer 2%');
        $this->assertSame(1100000, array_sum($a));
    }

    public function test_two_technicians_split_evenly_and_detailer_gets_extra_one_percent(): void
    {
        [$invoice, $t, $d] = $this->job(2, 1);
        $this->release($invoice);
        $a = $this->amounts($invoice);

        $this->assertSame(400000, $a[$t[0]->id]);
        $this->assertSame(400000, $a[$t[1]->id]);
        $this->assertSame(300000, $a[$d[0]->id], 'detailer 3%');
        $this->assertSame(1100000, array_sum($a), 'total tetap 11%');
    }

    public function test_single_technician_and_detailer_keep_the_eleven_percent_pool(): void
    {
        [$invoice, $t, $d] = $this->job(1, 1);
        $this->release($invoice);
        $a = $this->amounts($invoice);

        $this->assertSame(800000, $a[$t[0]->id]);
        $this->assertSame(300000, $a[$d[0]->id]);
    }

    public function test_extra_detailer_share_is_split_between_detailers(): void
    {
        [$invoice, $t, $d] = $this->job(1, 2);
        $this->release($invoice);
        $a = $this->amounts($invoice);

        $this->assertSame(150000, $a[$d[0]->id]);
        $this->assertSame(150000, $a[$d[1]->id]);
        $this->assertSame(800000, $a[$t[0]->id]);
    }

    public function test_without_a_detailer_the_technicians_share_nine_percent(): void
    {
        [$invoice, $t] = $this->job(3, 0);
        $this->release($invoice);
        $a = $this->amounts($invoice);

        foreach ($t as $tech) {
            $this->assertSame(300000, $a[$tech->id]);
        }
        $this->assertSame(900000, array_sum($a));
    }

    public function test_commission_base_is_the_price_after_discount(): void
    {
        [$invoice, $t, $d] = $this->job(3, 1, 8000000, 10000000); // diskon 2 juta
        $this->release($invoice);
        $a = $this->amounts($invoice);

        $this->assertSame(240000, $a[$t[0]->id], '3% dari 8.000.000');
        $this->assertSame(160000, $a[$d[0]->id], '2% dari 8.000.000');
    }

    public function test_rounding_remainder_never_loses_a_rupiah(): void
    {
        [$invoice] = $this->job(3, 1, 3333333); // pool 11% tidak habis dibagi
        $this->release($invoice);

        $this->assertSame((int) round(3333333 * 0.11), array_sum($this->amounts($invoice)));
    }

    public function test_a_per_employee_commission_override_still_applies(): void
    {
        [$invoice, $t] = $this->job(3, 1);
        $t[0]->update(['commission_mode' => 'none']);
        $this->release($invoice);
        $a = $this->amounts($invoice);

        $this->assertSame(0, $a[$t[0]->id]);
        $this->assertSame(300000, $a[$t[1]->id]);
    }
}
