<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreativeTaskDeliverable extends Model
{
    protected $fillable = ['creative_task_id', 'version', 'file_path', 'note', 'uploaded_by'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(CreativeTask::class, 'creative_task_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
