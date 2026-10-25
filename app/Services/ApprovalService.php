<?php

namespace App\Services;

use App\Models\ApprovalAction;
use App\Models\ApprovalFlowStep;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\Notifications\ApprovalRequestDecided;
use App\Notifications\ApprovalRequestSubmitted;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalService
{
    public function currentStep(ApprovalRequest $request): ?ApprovalFlowStep
    {
        return $request->flow->steps->firstWhere('step_order', $request->current_step);
    }

    public function canAct(ApprovalRequest $request, User $actor): bool
    {
        if (!$request->isPending()) {
            return false;
        }

        // Superadmin is the ultimate authority over every approval flow — same
        // convention as branch scoping elsewhere (CustomerController, WorkOrderController,
        // etc. all special-case hasRole('superadmin')). Without this, a superadmin who
        // doesn't personally hold the step's approver_role (e.g. 'manager_operasional')
        // could never act on — or even see, via ApprovalController::index() — a pending
        // request, even though they can see the aggregate counts on /owner-monitoring.
        if ($actor->hasRole('superadmin')) {
            return true;
        }

        $step = $this->currentStep($request);
        if (!$step || !$actor->hasRole($step->approver_role)) {
            return false;
        }

        if ($step->same_branch_as_subject) {
            $subjectBranchId = $request->approvable?->branch_id;
            if ($subjectBranchId !== null) {
                $allowed = $actor->branches()->pluck('branches.id')->map(fn ($id) => (int) $id)->all();
                if (!in_array((int) $subjectBranchId, $allowed, true)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function approve(ApprovalRequest $request, User $actor, ?string $note = null): ApprovalRequest
    {
        if (!$this->canAct($request, $actor)) {
            throw ValidationException::withMessages(['approval' => 'Anda tidak berhak menyetujui permintaan ini di tahap saat ini.']);
        }

        return DB::transaction(function () use ($request, $actor, $note) {
            $locked = ApprovalRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();

            if (!$locked->isPending()) {
                throw ValidationException::withMessages(['approval' => 'Permintaan ini sudah diproses sebelumnya.']);
            }

            $step = $this->currentStep($locked);

            ApprovalAction::create([
                'approval_request_id' => $locked->id,
                'step_order'          => $step->step_order,
                'acted_by'            => $actor->id,
                'action'              => 'approved',
                'note'                => $note,
                'acted_at'            => now(),
            ]);

            $nextStep = $locked->flow->steps->firstWhere('step_order', $step->step_order + 1);

            if ($nextStep) {
                $locked->update(['current_step' => $nextStep->step_order]);
                User::role($nextStep->approver_role)->get()
                    ->each(fn (User $approver) => $approver->notify(new ApprovalRequestSubmitted($locked)));
            } else {
                $locked->update(['status' => 'approved']);
                $locked->requester?->notify(new ApprovalRequestDecided($locked, 'approved'));
                $this->notifyApprovable($locked, 'approved');
            }

            return $locked->fresh();
        });
    }

    public function reject(ApprovalRequest $request, User $actor, string $note): ApprovalRequest
    {
        if (!$this->canAct($request, $actor)) {
            throw ValidationException::withMessages(['approval' => 'Anda tidak berhak menolak permintaan ini di tahap saat ini.']);
        }

        return DB::transaction(function () use ($request, $actor, $note) {
            $locked = ApprovalRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();

            if (!$locked->isPending()) {
                throw ValidationException::withMessages(['approval' => 'Permintaan ini sudah diproses sebelumnya.']);
            }

            $step = $this->currentStep($locked);

            ApprovalAction::create([
                'approval_request_id' => $locked->id,
                'step_order'          => $step->step_order,
                'acted_by'            => $actor->id,
                'action'              => 'rejected',
                'note'                => $note,
                'acted_at'            => now(),
            ]);

            $locked->update(['status' => 'rejected']);
            $locked->requester?->notify(new ApprovalRequestDecided($locked, 'rejected'));
            $this->notifyApprovable($locked, 'rejected');

            return $locked->fresh();
        });
    }

    /**
     * Generic hook: if the approvable model defines onApprovalDecision(),
     * call it once the request reaches a final state. Keeps this service
     * reusable across modules — each approvable model owns its own reaction
     * (e.g. EmployeeClaim updates its own status column) instead of this
     * service knowing about every model that uses it.
     */
    private function notifyApprovable(ApprovalRequest $request, string $finalStatus): void
    {
        $approvable = $request->approvable;
        if ($approvable && method_exists($approvable, 'onApprovalDecision')) {
            $approvable->onApprovalDecision($finalStatus, $request);
        }
    }

    /** Users allowed to act on the current step of a pending request (for notifications & queue listing). */
    public function pendingApproversQuery(string $role)
    {
        return User::role($role);
    }
}
