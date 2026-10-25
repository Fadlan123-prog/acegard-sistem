<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function __construct(private ApprovalService $approvals) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $roleNames = $user->getRoleNames()->toArray();

        $query = ApprovalRequest::with(['flow', 'requester', 'approvable'])
            ->where('status', 'pending');

        // Superadmin sees every pending request regardless of which role the
        // current step requires — same bypass as ApprovalService::canAct(),
        // otherwise a superadmin who isn't personally e.g. 'manager_operasional'
        // would see nothing here even though they can approve/reject anything.
        if (!$user->hasRole('superadmin')) {
            $query->whereExists(function ($subquery) use ($roleNames) {
                $subquery->select(DB::raw(1))
                    ->from('approval_flow_steps')
                    ->whereColumn('approval_flow_steps.approval_flow_id', 'approval_requests.approval_flow_id')
                    ->whereColumn('approval_flow_steps.step_order', 'approval_requests.current_step')
                    ->whereIn('approval_flow_steps.approver_role', $roleNames);
            });
        }

        $requests = $query->latest()->paginate(20);

        return view('approvals.index', compact('requests'));
    }

    public function approve(Request $request, ApprovalRequest $approvalRequest)
    {
        $data = $request->validate(['note' => ['nullable', 'string', 'max:500']]);

        $this->approvals->approve($approvalRequest, $request->user(), $data['note'] ?? null);

        return back()->with('success', 'Permintaan berhasil disetujui.');
    }

    public function reject(Request $request, ApprovalRequest $approvalRequest)
    {
        $data = $request->validate([
            'note' => ['required', 'string', 'max:500'],
        ], [
            'note.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $this->approvals->reject($approvalRequest, $request->user(), $data['note']);

        return back()->with('success', 'Permintaan berhasil ditolak.');
    }
}
