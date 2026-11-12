<?php

namespace Tests\Feature;

use App\Models\CreativeTask;
use App\Notifications\CreativeTaskAssigned;
use App\Notifications\CreativeTaskCancelled;
use App\Notifications\CreativeTaskReassigned;
use App\Notifications\CreativeTaskUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Kelola tugas Team Creative: hanya Strategi Pengembangan/superadmin yang membuat, mengubah, mengalihkan, dan membatalkan. */
class CreativeTaskManageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function task(\App\Models\User $by, \App\Models\User $to, array $over = []): CreativeTask
    {
        return CreativeTask::create(array_merge([
            'assigned_by' => $by->id, 'assigned_to' => $to->id, 'title' => 'Poster', 'brief' => 'Brief awal',
            'priority' => 'normal', 'status' => 'in_progress',
        ], $over));
    }

    private function payload(\App\Models\User $to, array $over = []): array
    {
        return array_merge(['assigned_to' => $to->id, 'title' => 'Poster', 'brief' => 'Brief awal', 'priority' => 'normal'], $over);
    }

    public function test_designer_and_videographer_cannot_create_edit_or_cancel_tasks(): void
    {
        $strategist = $this->userWithRole('strategi_pengembangan');
        $designer = $this->userWithRole('designer');
        $video = $this->userWithRole('videographer');
        $task = $this->task($strategist, $designer);

        foreach ([$designer, $video] as $actor) {
            $this->actingAs($actor)->get(route('creative-tasks.create'))->assertForbidden();
            $this->actingAs($actor)->post(route('creative-tasks.store'), $this->payload($video))->assertForbidden();
            $this->actingAs($actor)->get(route('creative-tasks.edit', $task))->assertForbidden();
            $this->actingAs($actor)->put(route('creative-tasks.update', $task), $this->payload($video, ['title' => 'Dibajak']))->assertForbidden();
            $this->actingAs($actor)->post(route('creative-tasks.cancel', $task), ['cancel_reason' => 'x'])->assertForbidden();
        }

        $this->assertSame('Poster', $task->fresh()->title);
        $this->assertSame(1, CreativeTask::count());
    }

    public function test_strategist_edits_a_task_and_the_assignee_is_notified(): void
    {
        Notification::fake();
        $strategist = $this->userWithRole('strategi_pengembangan');
        $designer = $this->userWithRole('designer');
        $task = $this->task($strategist, $designer);

        $this->actingAs($strategist)->get(route('creative-tasks.edit', $task))->assertOk()->assertSee('Brief awal');
        $this->actingAs($strategist)->put(route('creative-tasks.update', $task), $this->payload($designer, [
            'brief' => 'Brief baru', 'due_date' => now()->addDays(3)->toDateString(), 'priority' => 'high',
        ]))->assertRedirect(route('creative-tasks.show', $task));

        $task->refresh();
        $this->assertSame('Brief baru', $task->brief);
        $this->assertSame('high', $task->priority);
        Notification::assertSentTo($designer, CreativeTaskUpdated::class);
        Notification::assertNotSentTo($designer, CreativeTaskAssigned::class);
    }

    public function test_reassigning_notifies_the_new_and_the_old_assignee(): void
    {
        Notification::fake();
        $strategist = $this->userWithRole('strategi_pengembangan');
        $designer = $this->userWithRole('designer');
        $video = $this->userWithRole('videographer');
        $task = $this->task($strategist, $designer);

        $this->actingAs($strategist)->put(route('creative-tasks.update', $task), $this->payload($video))->assertRedirect();

        $this->assertSame($video->id, (int) $task->fresh()->assigned_to);
        Notification::assertSentTo($video, CreativeTaskAssigned::class);
        Notification::assertSentTo($designer, CreativeTaskReassigned::class);
        Notification::assertNotSentTo($designer, CreativeTaskUpdated::class);
    }

    public function test_reassign_target_must_be_designer_or_videographer(): void
    {
        $strategist = $this->userWithRole('strategi_pengembangan');
        $designer = $this->userWithRole('designer');
        $teknisi = $this->userWithRole('teknisi');
        $task = $this->task($strategist, $designer);

        $this->actingAs($strategist)->put(route('creative-tasks.update', $task), $this->payload($teknisi))->assertStatus(422);
        $this->assertSame($designer->id, (int) $task->fresh()->assigned_to);
    }

    public function test_cancel_requires_a_reason_notifies_and_blocks_further_uploads(): void
    {
        Notification::fake();
        Storage::fake('public');
        $strategist = $this->userWithRole('strategi_pengembangan');
        $designer = $this->userWithRole('designer');
        $task = $this->task($strategist, $designer);

        $this->actingAs($strategist)->post(route('creative-tasks.cancel', $task), [])->assertSessionHasErrors('cancel_reason');
        $this->assertSame('in_progress', $task->fresh()->status);

        $this->actingAs($strategist)->post(route('creative-tasks.cancel', $task), ['cancel_reason' => 'Kampanye dibatalkan'])->assertRedirect();
        $task->refresh();
        $this->assertSame('cancelled', $task->status);
        $this->assertSame('Kampanye dibatalkan', $task->cancel_reason);
        Notification::assertSentTo($designer, CreativeTaskCancelled::class);

        // penerima tidak bisa lagi mengunggah, dan tugas tidak bisa diubah/dibatalkan ulang
        $this->actingAs($designer)->post(route('creative-tasks.deliverables.store', $task), ['file' => UploadedFile::fake()->image('a.jpg')])->assertStatus(422);
        $this->assertSame(0, $task->deliverables()->count());
        $this->actingAs($strategist)->get(route('creative-tasks.edit', $task))->assertStatus(422);
        $this->actingAs($strategist)->post(route('creative-tasks.cancel', $task), ['cancel_reason' => 'lagi'])->assertStatus(422);

        $this->actingAs($strategist)->get(route('creative-tasks.show', $task))->assertOk()->assertSee('Tugas dibatalkan')->assertSee('Kampanye dibatalkan');
        $this->actingAs($strategist)->get(route('creative-tasks.index'))->assertOk()->assertSee('Dibatalkan');
    }

    public function test_done_tasks_cannot_be_edited_or_cancelled(): void
    {
        $strategist = $this->userWithRole('strategi_pengembangan');
        $designer = $this->userWithRole('designer');
        $task = $this->task($strategist, $designer, ['status' => 'done']);

        $this->actingAs($strategist)->get(route('creative-tasks.edit', $task))->assertStatus(422);
        $this->actingAs($strategist)->post(route('creative-tasks.cancel', $task), ['cancel_reason' => 'x'])->assertStatus(422);
    }

    public function test_a_strategist_cannot_manage_another_strategists_task_but_superadmin_can(): void
    {
        $s1 = $this->userWithRole('strategi_pengembangan');
        $s2 = $this->userWithRole('strategi_pengembangan');
        $designer = $this->userWithRole('designer');
        $super = $this->userWithRole('superadmin');
        $task = $this->task($s1, $designer);

        $this->actingAs($s2)->get(route('creative-tasks.edit', $task))->assertForbidden();
        $this->actingAs($s2)->post(route('creative-tasks.cancel', $task), ['cancel_reason' => 'x'])->assertForbidden();

        $this->actingAs($super)->get(route('creative-tasks.edit', $task))->assertOk();
        $this->actingAs($super)->post(route('creative-tasks.cancel', $task), ['cancel_reason' => 'Atas perintah direksi'])->assertRedirect();
        $this->assertSame('cancelled', $task->fresh()->status);
    }

    public function test_manage_buttons_show_only_to_the_task_owner_while_open(): void
    {
        $strategist = $this->userWithRole('strategi_pengembangan');
        $designer = $this->userWithRole('designer');
        $task = $this->task($strategist, $designer);

        $this->actingAs($strategist)->get(route('creative-tasks.show', $task))->assertSee('Ubah Tugas / Ganti Penerima')->assertSee('Batalkan Tugas');
        $this->actingAs($designer)->get(route('creative-tasks.show', $task))->assertDontSee('Ubah Tugas / Ganti Penerima')->assertDontSee('Batalkan Tugas');
    }
}
