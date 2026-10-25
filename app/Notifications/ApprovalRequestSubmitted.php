<?php

namespace App\Notifications;

use App\Models\ApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApprovalRequestSubmitted extends Notification
{
    use Queueable;

    public function __construct(public ApprovalRequest $approvalRequest) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $approvable = $this->approvalRequest->approvable;
        $summary = $approvable && method_exists($approvable, 'approvalSummary')
            ? $approvable->approvalSummary()
            : class_basename($this->approvalRequest->approvable_type) . ' #' . $this->approvalRequest->approvable_id;

        return [
            'type'                => 'approval_request_submitted',
            'approval_request_id' => $this->approvalRequest->id,
            'flow'                => $this->approvalRequest->flow->name,
            'url'                 => route('approvals.index', [], false),
            'message'             => "Menunggu persetujuan Anda: {$this->approvalRequest->flow->name} — {$summary}.",
        ];
    }
}
