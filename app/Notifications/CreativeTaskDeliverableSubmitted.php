<?php

namespace App\Notifications;

use App\Models\CreativeTask;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CreativeTaskDeliverableSubmitted extends Notification
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
            'type'    => 'creative_task_deliverable_submitted',
            'task_id' => $this->task->id,
            'url'     => route('creative-tasks.show', $this->task, false),
            'message' => "\"{$this->task->title}\" ada deliverable baru dari {$this->task->assignee?->name}, perlu direview.",
        ];
    }
}
