<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\ExportRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MonthlyPayrollExportFormatTest extends TestCase
{
    use RefreshDatabase;

    private function doExport(string $format, string $month): array
    {
        $branch = Branch::factory()->create();
        Employee::factory()->create(['branch_id' => $branch->id, 'monthly_salary' => 5000000, 'job_position' => 'Admin']);
        $admin = $this->userWithRole('admin', $branch);

        $this->actingAs($admin)->get(route('attendances.monthlyReport.export', ['month' => $month, 'branch_id' => $branch->id, 'format' => $format]))->assertRedirect();
        $req = ExportRequest::latest('id')->first();
        $check = $this->actingAs($admin)->getJson(route('export-status.check', $req->id))->json();
        $download = $this->actingAs($admin)->get(route('export-status.download', $req->id));

        return [$req->fresh(), $check, $download];
    }

    public function test_pdf_for_previous_month_is_a_pdf_file(): void
    {
        Storage::fake('local');
        $month = now()->subMonth()->format('Y-m');
        [$req, $check, $download] = $this->doExport('pdf', $month);

        $this->assertSame('attendance_pdf', $req->type);
        $this->assertStringEndsWith('.pdf', $req->file_name);
        $this->assertStringEndsWith('.pdf', $req->file_path);
        $this->assertStringContainsString('.pdf', (string) $download->headers->get('content-disposition'));
    }

    public function test_report_page_links_are_not_double_encoded_so_pdf_really_requests_pdf(): void
    {
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $month = now()->subMonth()->format('Y-m');

        $html = $this->actingAs($admin)->get(route('attendances.monthlyReport', ['month' => $month, 'branch_id' => $branch->id]))->assertOk()->getContent();

        $this->assertStringNotContainsString('&amp;amp;', $html, 'tautan di-escape dua kali: parameter format hilang dan PDF jadi Excel');
        $this->assertMatchesRegularExpression('/export\?month=' . $month . '&amp;branch_id=' . $branch->id . '&amp;format=pdf"/', $html);
        $this->assertMatchesRegularExpression('/export\?month=' . $month . '&amp;branch_id=' . $branch->id . '&amp;format=excel"/', $html);

        // klik tautan PDF persis seperti browser (decode HTML satu kali) -> harus menghasilkan permintaan PDF
        preg_match('/href="([^"]*format=pdf)"/', $html, $m);
        $this->actingAs($admin)->get(html_entity_decode($m[1]))->assertRedirect();
        $this->assertSame('attendance_pdf', ExportRequest::latest('id')->value('type'));
    }

    /** Komponen <x-ui.*> sudah meng-escape href; href="{{ ... }}" di tag komponen = escape ganda. Gunakan :href. */
    public function test_no_ui_component_tag_passes_an_interpolated_url_with_query_parameters(): void
    {
        $offenders = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'))) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            foreach (file($file->getPathname()) as $n => $line) {
                if (preg_match('/<x-ui\.[a-z-]+[^>]*[^:]href="\{\{[^}]*(request\(\)|array_merge|, *\[)/', $line)) {
                    $offenders[] = str_replace(base_path() . '/', '', $file->getPathname()) . ':' . ($n + 1);
                }
            }
        }

        $this->assertSame([], $offenders, "Pakai :href=\"route(...)\" pada komponen x-ui agar '&' tidak di-escape dua kali");
    }
}
