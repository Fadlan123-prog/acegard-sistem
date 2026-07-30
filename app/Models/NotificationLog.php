<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'branch_id', 'type', 'channel', 'recipient',
        'message', 'status', 'error',
        'relatable_type', 'relatable_id',
    ];

    public function relatable()
    {
        return $this->morphTo();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
