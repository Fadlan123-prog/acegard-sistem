<?php

namespace App\Notifications;

use App\Models\NotificationConfig;
use Illuminate\Notifications\Notification;

class AutomatedAlert extends Notification
{
    public function __construct(
        private string $message,
        private string $type,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'    => 'automated_alert',
            'subtype' => $this->type,
            'message' => $this->message,
            'label'   => NotificationConfig::$types[$this->type] ?? $this->type,
        ];
    }
}
