<?php

namespace App\Notifications;

use App\Models\CreativeTask;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CreativeTaskUpdated extends Notification
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
            'type'    => 'creative_task_updated',
            'task_id' => $this->task->id,
            'url'     => route('creative-tasks.show', $this->task, false),
            'message' => "Tugas \"{$this->task->title}\" diperbarui oleh {$this->task->assigner?->name}. Periksa brief dan deadline terbaru.",
        ];
    }
}
