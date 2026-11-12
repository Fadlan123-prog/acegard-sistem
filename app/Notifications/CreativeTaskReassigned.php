<?php

namespace App\Notifications;

use App\Models\CreativeTask;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/** Dikirim ke penerima LAMA saat tugas dialihkan ke orang lain. */
class CreativeTaskReassigned extends Notification
{
    use Queueable;

    public function __construct(public CreativeTask $task) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'    => 'creative_task_reassigned',
            'task_id' => $this->task->id,
            'url'     => route('creative-tasks.show', $this->task, false),
            'message' => "Tugas \"{$this->task->title}\" dialihkan ke {$this->task->assignee?->name}. Anda tidak perlu mengerjakannya lagi.",
        ];
    }
}
