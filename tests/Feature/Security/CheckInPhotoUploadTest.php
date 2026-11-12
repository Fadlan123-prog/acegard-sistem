<?php

namespace Tests\Feature\Security;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regression test for the RCE fix in AttendanceApiController::storeBase64Photo().
 * Before the fix, the extension was taken directly from the client-supplied
 * data URI header with no allowlist (`data:image/php;base64,...` -> a .php file
 * written straight into the public web root). See app/Http/Controllers/Api/AttendanceApiController.php.
 */
class CheckInPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_PNG_B64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    protected function setUp(): void
    {
        parent::setUp();
        File::deleteDirectory(Storage::disk('local')->path('checkin-photos'));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(Storage::disk('local')->path('checkin-photos'));
        parent::tearDown();
    }

    public function test_malicious_extension_is_rejected_and_no_php_file_is_written(): void
    {
        $employee = Employee::factory()->create(['mobile_token' => 'test-token-123']);

        $malicious = 'data:image/php;base64,' . base64_encode('<?php echo "PWNED"; ?>');

        $response = $this->withHeader('Authorization', 'Bearer test-token-123')
            ->postJson('/api/attendance/check-in', [
                'lat'   => -6.2,
                'lng'   => 106.8,
                'photo' => $malicious,
            ]);

        $response->assertSuccessful();

        $dir = Storage::disk('local')->path('checkin-photos');
        $this->assertDirectoryDoesNotContainPhpFiles($dir);

        $this->assertDatabaseHas('attendances', [
            'employee_id'    => $employee->id,
            'check_in_photo' => null,
        ]);
    }

    public function test_php_disguised_as_png_extension_is_rejected_by_content_validation(): void
    {
        $employee = Employee::factory()->create(['mobile_token' => 'test-token-456']);

        // Allowed extension, but the decoded bytes are not a real image —
        // getimagesizefromstring() must catch this even though the regex passed.
        $disguised = 'data:image/png;base64,' . base64_encode('<?php echo "PWNED"; ?>');

        $this->withHeader('Authorization', 'Bearer test-token-456')
            ->postJson('/api/attendance/check-in', [
                'lat'   => -6.2,
                'lng'   => 106.8,
                'photo' => $disguised,
            ])
            ->assertSuccessful();

        $this->assertDatabaseHas('attendances', [
            'employee_id'    => $employee->id,
            'check_in_photo' => null,
        ]);

        $this->assertDirectoryDoesNotContainPhpFiles(Storage::disk('local')->path('checkin-photos'));
    }

    public function test_a_genuine_png_upload_still_works(): void
    {
        $employee = Employee::factory()->create(['mobile_token' => 'test-token-789']);

        $valid = 'data:image/png;base64,' . self::VALID_PNG_B64;

        $this->withHeader('Authorization', 'Bearer test-token-789')
            ->postJson('/api/attendance/check-in', [
                'lat'   => -6.2,
                'lng'   => 106.8,
                'photo' => $valid,
            ])
            ->assertSuccessful();

        $attendance = \App\Models\Attendance::where('employee_id', $employee->id)->first();
        $this->assertNotNull($attendance->check_in_photo);
        $this->assertStringEndsWith('.png', $attendance->check_in_photo);
        $this->assertTrue(Storage::disk('local')->exists($attendance->check_in_photo));
        $this->assertStringStartsNotWith('storage/', $attendance->check_in_photo);
        $this->assertFileDoesNotExist(public_path($attendance->check_in_photo));
    }

    private function assertDirectoryDoesNotContainPhpFiles(string $dir): void
    {
        if (!is_dir($dir)) {
            $this->assertTrue(true); // nothing was written at all — also a pass
            return;
        }

        $phpFiles = collect(scandir($dir))->filter(fn ($f) => str_ends_with($f, '.php'));
        $this->assertTrue($phpFiles->isEmpty(), 'Found .php file(s) in ' . $dir . ': ' . $phpFiles->implode(', '));
    }
}
