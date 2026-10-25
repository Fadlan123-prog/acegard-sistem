<?php

namespace App\Notifications;

use App\Models\CreativeTask;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CreativeTaskRevisionRequested extends Notification
{
    use Queueable;

    public function __construct(public CreativeTask $task, public ?string $note = null) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'    => 'creative_task_revision_requested',
            'task_id' => $this->task->id,
            'url'     => route('creative-tasks.show', $this->task, false),
            'message' => "\"{$this->task->title}\" diminta revisi" . ($this->note ? ": {$this->note}" : '.'),
        ];
    }
}
