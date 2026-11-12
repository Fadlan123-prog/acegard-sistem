<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerPpf;
use App\Models\CustomerPpfInstallation;
use App\Models\Employee;
use App\Models\PpfInvoice;
use App\Services\DocumentNumberService;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Banyak pengguna menyimpan dokumen bersamaan: nomor tidak boleh bentrok / gagal 500. */
class NumberingConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_sequential_numbers_use_max_not_count_so_deletions_do_not_collide(): void
    {
        $docs = app(DocumentNumberService::class);
        $branch = Branch::factory()->create();
        $customer = CustomerPpf::factory()->create(['branch_id' => $branch->id]);
        foreach (['PPF-20261031-0001', 'PPF-20261031-0002', 'PPF-20261031-0003'] as $n) {
            DB::table('ppf_invoices')->insert(['branch_id' => $branch->id, 'customer_ppf_id' => $customer->id, 'customer_ppf_installation_id' => 0 + $this->installationId($customer, $branch),
                'invoice_number' => $n, 'invoice_date' => now()->toDateString(), 'created_at' => now(), 'updated_at' => now()]);
        }
        DB::table('ppf_invoices')->where('invoice_number', 'PPF-20261031-0001')->delete(); // hapus yang pertama

        $this->assertSame('PPF-20261031-0004', $docs->nextSequential('ppf_invoices', 'invoice_number', 'PPF-20261031-'));
        $this->assertSame('PPF-20261101-0001', $docs->nextSequential('ppf_invoices', 'invoice_number', 'PPF-20261101-'));
    }

    private int $installation = 0;

    private function installationId($customer, $branch): int
    {
        if ($this->installation) {
            return $this->installation;
        }
        $productId = DB::table('ppf_products')->insertGetId(['name' => 'P', 'brand' => 'B', 'thickness_microns' => 150, 'finish' => 'g', 'created_at' => now(), 'updated_at' => now()]);

        return $this->installation = CustomerPpfInstallation::create([
            'customer_ppf_id' => $customer->id, 'ppf_product_id' => $productId, 'branch_id' => $branch->id, 'install_type' => 'fullbody',
            'install_date' => now()->toDateString(), 'warranty_code' => 'W-1', 'warranty_start' => now()->toDateString(),
            'warranty_years' => 5, 'warranty_end' => now()->addYears(5)->toDateString(),
        ])->id;
    }

    public function test_retry_on_duplicate_regenerates_the_number(): void
    {
        $docs = app(DocumentNumberService::class);
        $calls = 0;

        $result = $docs->retryOnDuplicate(function () use (&$calls) {
            $calls++;
            if ($calls < 3) {
                throw new UniqueConstraintViolationException('mysql', 'insert', [], new \Exception('dup'));
            }

            return 'ok';
        });

        $this->assertSame('ok', $result);
        $this->assertSame(3, $calls);
    }

    public function test_ppf_invoice_numbers_stay_unique_after_a_delete_and_new_save(): void
    {
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $customer = CustomerPpf::factory()->create(['branch_id' => $branch->id]);
        $installationId = $this->installationId($customer, $branch);
        $payload = ['payment_method' => 'cash', 'price' => 5000000, 'discount' => 0, 'downpayment' => 0];

        // tiga installation berbeda tidak diperlukan: invoice duplikat 30 detik dicegah, jadi variasikan harga
        $numbers = [];
        foreach ([5000000, 5100000, 5200000] as $price) {
            $this->actingAs($admin)->post(route('ppf-invoices.store', $installationId), array_merge($payload, ['price' => $price]))->assertRedirect();
            $numbers[] = PpfInvoice::latest('id')->value('invoice_number');
        }
        PpfInvoice::where('invoice_number', $numbers[0])->delete();
        $this->actingAs($admin)->post(route('ppf-invoices.store', $installationId), array_merge($payload, ['price' => 5300000]))->assertRedirect();
        $numbers[] = PpfInvoice::latest('id')->value('invoice_number');

        $this->assertCount(4, array_unique($numbers), 'tidak ada nomor kembar');
        $this->assertSame(1, PpfInvoice::where('invoice_number', $numbers[3])->count());
    }

    public function test_warranty_claim_numbers_are_sequential_and_unique(): void
    {
        $branch = Branch::factory()->create();
        $customer = Customer::factory()->create(['branch_id' => $branch->id, 'card_number' => 'C-1', 'phone_number' => '0812-0000-1111']);

        $this->post(route('warranty.lookup'), ['card_number' => 'C-1', 'phone_last4' => '1111'])->assertRedirect();
        foreach ([1, 2, 3] as $i) {
            $this->post(route('warranty.claim.store'), ['card_number' => 'C-1', 'type' => 'car_film', 'id' => $customer->id, 'complaint' => "keluhan $i"])->assertRedirect();
        }

        $numbers = DB::table('warranty_claims')->orderBy('id')->pluck('claim_number')->all();
        $prefix = 'CLM-' . now()->format('Ym') . '-';
        $this->assertSame([$prefix . '0001', $prefix . '0002', $prefix . '0003'], $numbers);
    }

    public function test_a_second_withdrawal_request_after_a_rejection_does_not_collide(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-15 10:00:00', 'Asia/Jakarta'));
        $branch = Branch::factory()->create();
        $emp = Employee::factory()->create(['branch_id' => $branch->id, 'job_position' => 'Teknisi']);
        $user = $this->userWithRole('teknisi', $branch);
        $user->update(['employee_id' => $emp->id]);
        $customer = Customer::factory()->create(['branch_id' => $branch->id]);

        $mk = fn () => DB::table('commissions')->insert(['employees_id' => $emp->id, 'customer_id' => $customer->id, 'amount' => 1000,
            'role' => 'installer' . uniqid(), 'status' => 'eligible', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $mk();
        $this->actingAs($user)->post(route('teknisi.withdrawal.request'))->assertRedirect();
        DB::table('commission_payment_batches')->update(['status' => 'cancelled']);     // ditolak admin
        DB::table('commissions')->update(['commission_payment_batch_id' => null]);

        $mk();
        $this->actingAs($user)->post(route('teknisi.withdrawal.request'))->assertSessionHas('success');

        $this->assertSame(2, DB::table('commission_payment_batches')->count());
        Carbon::setTestNow();
    }
}
