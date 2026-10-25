<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalFlowStep extends Model
{
    protected $fillable = ['approval_flow_id', 'step_order', 'approver_role', 'same_branch_as_subject'];

    protected $casts = [
        'same_branch_as_subject' => 'boolean',
    ];

    public function flow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id');
    }
}
