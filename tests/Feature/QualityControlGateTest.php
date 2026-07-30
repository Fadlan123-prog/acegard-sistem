<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Regression tests for the Phase 5 QC gate: technician completion sets qc_status=pending,
 * which blocks InvoiceController::recordPelunasan() until a supervisor approves it via
 * WorkOrderController::qcApprove()/qcReject(). Also proves the gate does NOT retroactively
 * block invoices completed before it existed (qc_status IS NULL must never block).
 */
class QualityControlGateTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;
    private Employee $tukang;
    private Customer $customer;
    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch  = Branch::factory()->create();
        $this->tukang  = Employee::factory()->create(['branch_id' => $this->branch->id, 'job_position' => 'Teknisi']);
        $this->customer = Customer::factory()->create(['branch_id' => $this->branch->id, 'tukang_id' => $this->tukang->id]);
        $this->invoice  = Invoice::factory()->create(['customer_id' => $this->customer->id]);
    }

    private function technicianUser(): \App\Models\User
    {
        $user = $this->userWithRole('teknisi', $this->branch);
        $user->employee_id = $this->tukang->id;
        $user->save();

        return $user;
    }

    public function test_completing_a_job_sets_qc_status_to_pending(): void
    {
        $this->invoice->update(['work_status' => 'accepted', 'accepted_at' => now()]);
        $tech = $this->technicianUser();

        $this->actingAs($tech)->post(route('teknisi.job.complete', $this->customer->id), [
            'job_photo'       => UploadedFile::fake()->image('job.jpg'),
            'checklist_photo' => UploadedFile::fake()->image('checklist.jpg'),
        ])->assertRedirect();

        $this->invoice->refresh();
        $this->assertSame('completed', $this->invoice->work_status);
        $this->assertSame('pending', $this->invoice->qc_status);
    }

    public function test_pelunasan_is_blocked_while_qc_is_pending(): void
    {
        $this->invoice->update(['work_status' => 'completed', 'qc_status' => 'pending']);
        $staff = $this->userWithRole('admin', $this->branch);

        $this->actingAs($staff)
            ->post(route('invoice.pelunasan', $this->invoice->id), ['payment_method' => 'cash']);

        $this->invoice->refresh();
        $this->assertNull($this->invoice->pelunasan_at);
    }

    public function test_pelunasan_is_blocked_while_qc_failed(): void
    {
        $this->invoice->update(['work_status' => 'accepted', 'qc_status' => 'failed', 'qc_notes' => 'Perlu perbaikan sudut panel']);
        $staff = $this->userWithRole('admin', $this->branch);

        $this->actingAs($staff)
            ->post(route('invoice.pelunasan', $this->invoice->id), ['payment_method' => 'cash']);

        $this->invoice->refresh();
        $this->assertNull($this->invoice->pelunasan_at);
    }

    public function test_pelunasan_works_normally_when_qc_status_is_null_pre_existing_invoice(): void
    {
        // Simulates an invoice completed before the QC gate existed — must NOT be retroactively blocked.
        $this->invoice->update(['work_status' => 'completed', 'qc_status' => null]);
        $staff = $this->userWithRole('admin', $this->branch);

        $this->actingAs($staff)
            ->post(route('invoice.pelunasan', $this->invoice->id), ['payment_method' => 'cash']);

        $this->invoice->refresh();
        $this->assertNotNull($this->invoice->pelunasan_at);
    }

    public function test_supervisor_approving_qc_unblocks_pelunasan(): void
    {
        $this->invoice->update(['work_status' => 'completed', 'qc_status' => 'pending']);
        $supervisor = $this->userWithRole('manager_operasional', $this->branch);

        $this->actingAs($supervisor)
            ->post(route('work-orders.qc-approve', $this->customer->id))
            ->assertRedirect();

        $this->invoice->refresh();
        $this->assertSame('passed', $this->invoice->qc_status);
        $this->assertSame($supervisor->id, $this->invoice->qc_reviewed_by);

        $staff = $this->userWithRole('admin', $this->branch);
        $this->actingAs($staff)->post(route('invoice.pelunasan', $this->invoice->id), ['payment_method' => 'cash']);

        $this->invoice->refresh();
        $this->assertNotNull($this->invoice->pelunasan_at);
    }

    public function test_supervisor_rejecting_qc_sends_job_back_to_technician(): void
    {
        $this->invoice->update(['work_status' => 'completed', 'completed_at' => now(), 'qc_status' => 'pending']);
        $supervisor = $this->userWithRole('manager_operasional', $this->branch);

        $this->actingAs($supervisor)
            ->post(route('work-orders.qc-reject', $this->customer->id), [
                'qc_notes' => 'Sudut kanan belum rapi, tolong diperbaiki.',
            ])
            ->assertRedirect();

        $this->invoice->refresh();
        $this->assertSame('failed', $this->invoice->qc_status);
        $this->assertSame('accepted', $this->invoice->work_status);
        $this->assertNull($this->invoice->completed_at);
        $this->assertSame('Sudut kanan belum rapi, tolong diperbaiki.', $this->invoice->qc_notes);
    }

    public function test_qc_reject_requires_notes(): void
    {
        $this->invoice->update(['work_status' => 'completed', 'qc_status' => 'pending']);
        $supervisor = $this->userWithRole('manager_operasional', $this->branch);

        $this->actingAs($supervisor)
            ->post(route('work-orders.qc-reject', $this->customer->id), ['qc_notes' => ''])
            ->assertSessionHasErrors('qc_notes');

        $this->invoice->refresh();
        $this->assertSame('pending', $this->invoice->qc_status);
    }

    public function test_non_supervisor_cannot_approve_qc(): void
    {
        $this->invoice->update(['work_status' => 'completed', 'qc_status' => 'pending']);
        $marketing = $this->userWithRole('marketing', $this->branch);

        $this->actingAs($marketing)
            ->post(route('work-orders.qc-approve', $this->customer->id))
            ->assertForbidden();

        $this->invoice->refresh();
        $this->assertSame('pending', $this->invoice->qc_status);
    }
}
