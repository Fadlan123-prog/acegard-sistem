<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportRequest extends Model
{
    protected $fillable = [
        'type', 'params', 'status', 'file_path', 'file_name',
        'result_id', 'user_id', 'branch_id', 'error_message', 'completed_at',
    ];

    protected $casts = [
        'params'       => 'array',
        'completed_at' => 'datetime',
    ];

    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isProcessing(): bool { return $this->status === 'processing'; }
    public function isDone(): bool       { return $this->status === 'done'; }
    public function isFailed(): bool     { return $this->status === 'failed'; }

    public function markProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markDone(array $extra = []): void
    {
        $this->update(array_merge(['status' => 'done', 'completed_at' => now()], $extra));
    }

    public function markFailed(string $message): void
    {
        $this->update(['status' => 'failed', 'error_message' => $message, 'completed_at' => now()]);
    }
}
