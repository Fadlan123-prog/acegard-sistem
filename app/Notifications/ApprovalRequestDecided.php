<?php

namespace App\Notifications;

use App\Models\ApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApprovalRequestDecided extends Notification
{
    use Queueable;

    public function __construct(public ApprovalRequest $approvalRequest, public string $decision) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $label = $this->decision === 'approved' ? 'disetujui' : 'ditolak';

        return [
            'type'                => 'approval_request_decided',
            'approval_request_id' => $this->approvalRequest->id,
            'flow'                => $this->approvalRequest->flow->name,
            'decision'            => $this->decision,
            'message'             => "Permintaan Anda ({$this->approvalRequest->flow->name}) telah {$label}.",
        ];
    }
}
