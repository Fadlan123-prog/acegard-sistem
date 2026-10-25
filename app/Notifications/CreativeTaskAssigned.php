<?php

namespace App\Notifications;

use App\Models\CreativeTask;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CreativeTaskAssigned extends Notification
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
            'type'    => 'creative_task_assigned',
            'task_id' => $this->task->id,
            'url'     => route('creative-tasks.show', $this->task, false),
            'message' => "Tugas baru: \"{$this->task->title}\" dari {$this->task->assigner?->name}.",
        ];
    }
}
