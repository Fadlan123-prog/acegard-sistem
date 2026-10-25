<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ExportRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression test for a disk-root mismatch bug: GenerateExportJob::generateAttendancePdf()
 * used to save via the raw storage_path('app/exports/...') helper, while
 * ExportStatusController::download() checked Storage::disk('local') (root:
 * storage/app/private). The two never pointed at the same file, so the monthly
 * payroll PDF ("laporan gaji bulanan") always 404'd on download.
 */
class ExportDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_payroll_pdf_export_is_downloadable_after_processing(): void
    {
        $branch = Branch::factory()->create();
        $staff  = $this->userWithRole('admin', $branch);

        $exportRequest = ExportRequest::create([
            'type'      => 'attendance_pdf',
            'params'    => ['month' => now()->format('Y-m'), 'branch_id' => $branch->id],
            'file_name' => 'laporan-gaji-test.pdf',
            'status'    => 'pending',
            'user_id'   => $staff->id,
            'branch_id' => $branch->id,
        ]);

        // Mirrors the real flow: the status page polls this endpoint, which
        // synchronously runs the job if no queue worker has claimed it yet.
        $check = $this->actingAs($staff)->get(route('export-status.check', $exportRequest->id));
        $check->assertSuccessful();
        $check->assertJson(['status' => 'done']);
        $check->assertJsonStructure(['download_url']);

        $response = $this->actingAs($staff)
            ->get(route('export-status.download', $exportRequest->id));

        $response->assertSuccessful();
        $this->assertStringStartsWith('%PDF', $response->streamedContent());
    }
}
