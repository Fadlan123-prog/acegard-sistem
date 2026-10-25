<?php

namespace App\Traits;

use App\Models\ApprovalFlow;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\Notifications\ApprovalRequestSubmitted;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Attach to any model that needs to go through an approval_flows-configured
 * chain (see ApprovalService). The model must have its own status column
 * that the caller updates alongside the ApprovalRequest — this trait only
 * manages the approval bookkeeping, not the subject model's own status.
 */
trait HasApprovalFlow
{
    public function approvalRequest(): MorphOne
    {
        return $this->morphOne(ApprovalRequest::class, 'approvable');
    }

    public function submitForApproval(string $flowCode, int $requestedBy): ApprovalRequest
    {
        $flow = ApprovalFlow::with('steps')->where('code', $flowCode)->where('is_active', true)->firstOrFail();

        $request = $this->approvalRequest()->create([
            'approval_flow_id' => $flow->id,
            'current_step'     => 1,
            'status'           => 'pending',
            'requested_by'     => $requestedBy,
        ]);

        $firstStep = $flow->steps->firstWhere('step_order', 1);
        if ($firstStep) {
            User::role($firstStep->approver_role)->get()
                ->each(fn (User $approver) => $approver->notify(new ApprovalRequestSubmitted($request)));
        }

        return $request;
    }
}
