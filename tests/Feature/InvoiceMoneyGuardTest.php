<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Guard integritas uang pada invoice mobil (audit PR-H5 / PR-H6):
 * - pelunasan yang dikirim dua kali tidak mencatat ulang;
 * - rilis ulang komisi ditolak bila komisi sudah dibayar / masuk batch pencairan.
 */
class InvoiceMoneyGuardTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;
    private Customer $customer;
    private Invoice $invoice;
    private Employee $tukang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch   = Branch::factory()->create();
        $this->tukang   = Employee::factory()->create(['branch_id' => $this->branch->id, 'job_position' => 'Teknisi']);
        $this->customer = Customer::factory()->create(['branch_id' => $this->branch->id, 'tukang_id' => $this->tukang->id]);
        $this->invoice  = Invoice::factory()->create(['customer_id' => $this->customer->id]);
        $this->invoice->update(['work_status' => 'completed', 'qc_status' => null]);
    }

    public function test_second_pelunasan_request_does_not_overwrite_the_first(): void
    {
        $staff = $this->userWithRole('admin', $this->branch);

        $this->actingAs($staff)->post(route('invoice.pelunasan', $this->invoice->id), ['payment_method' => 'cash']);
        $first = $this->invoice->fresh();
        $this->assertNotNull($first->pelunasan_at);

        $this->actingAs($staff)->post(route('invoice.pelunasan', $this->invoice->id), ['payment_method' => 'transfer']);
        $second = $this->invoice->fresh();

        $this->assertEquals($first->pelunasan_at, $second->pelunasan_at);
        $this->assertSame('cash', $second->payment_method);
    }

    public function test_rerelease_is_refused_when_a_commission_is_already_paid(): void
    {
        $this->invoice->update(['pelunasan_at' => now(), 'status' => 'paid']);
        $id = DB::table('commissions')->insertGetId([
            'employees_id' => $this->tukang->id,
            'customer_id'  => $this->customer->id,
            'invoice_id'   => $this->invoice->id,
            'amount'       => 100000,
            'role'         => 'installer',
            'status'       => 'paid',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $super = $this->userWithRole('superadmin', $this->branch);

        $this->actingAs($super)
            ->post(route('invoice.rerelease-commission', $this->invoice->id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('commissions', ['id' => $id, 'status' => 'paid']);
    }
}
