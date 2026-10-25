<?php

namespace Tests\Feature;

use App\Models\CreativeTask;
use App\Notifications\CreativeTaskAssigned;
use App\Notifications\CreativeTaskDeliverableSubmitted;
use App\Notifications\CreativeTaskRevisionRequested;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreativeTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_lifecycle_assign_upload_request_revision_reupload_approve(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        Storage::fake('public');
        Notification::fake();

        $strategist = $this->userWithRole('strategi_pengembangan');
        $designer   = $this->userWithRole('designer');

        // 1. Assign
        $this->actingAs($strategist)->post(route('creative-tasks.store'), [
            'assigned_to' => $designer->id,
            'title'       => 'Desain poster promo Lebaran',
            'brief'       => 'Poster A3, tema Lebaran, warna brand.',
            'priority'    => 'high',
            'due_date'    => now()->addDays(5)->toDateString(),
        ])->assertRedirect();

        $task = CreativeTask::first();
        $this->assertSame('in_progress', $task->status);
        Notification::assertSentTo($designer, CreativeTaskAssigned::class);

        // A non-assignee designer cannot upload
        $otherDesigner = $this->userWithRole('designer');
        $this->actingAs($otherDesigner)
            ->post(route('creative-tasks.deliverables.store', $task), [
                'file' => UploadedFile::fake()->image('poster.jpg'),
            ])->assertForbidden();

        // 2. Designer uploads deliverable v1
        $this->actingAs($designer)->post(route('creative-tasks.deliverables.store', $task), [
            'file' => UploadedFile::fake()->image('poster-v1.jpg'),
            'note' => 'Draft pertama',
        ])->assertRedirect();

        $task->refresh();
        $this->assertSame('review', $task->status);
        $this->assertSame(1, $task->deliverables()->count());
        Notification::assertSentTo($strategist, CreativeTaskDeliverableSubmitted::class);

        // 3. Strategist requests revision
        $this->actingAs($strategist)->post(route('creative-tasks.revision', $task), [
            'note' => 'Logo kurang besar',
        ])->assertRedirect();

        $task->refresh();
        $this->assertSame('revision', $task->status);
        Notification::assertSentTo($designer, CreativeTaskRevisionRequested::class);

        // 4. Designer re-uploads v2
        $this->actingAs($designer)->post(route('creative-tasks.deliverables.store', $task), [
            'file' => UploadedFile::fake()->image('poster-v2.jpg'),
        ])->assertRedirect();

        $task->refresh();
        $this->assertSame('review', $task->status);
        $this->assertSame(2, $task->deliverables()->count());
        $this->assertSame(2, $task->deliverables()->max('version'));

        // 5. Strategist approves
        $this->actingAs($strategist)->post(route('creative-tasks.approve', $task))->assertRedirect();

        $task->refresh();
        $this->assertSame('done', $task->status);
    }

    public function test_designer_cannot_be_assigned_a_task_by_someone_else_role(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $strategist  = $this->userWithRole('strategi_pengembangan');
        $notCreative = $this->userWithRole('teknisi');

        $this->actingAs($strategist)->post(route('creative-tasks.store'), [
            'assigned_to' => $notCreative->id,
            'title'       => 'Test',
            'brief'       => 'Test',
            'priority'    => 'normal',
        ])->assertStatus(422);

        $this->assertDatabaseCount('creative_tasks', 0);
    }
}
