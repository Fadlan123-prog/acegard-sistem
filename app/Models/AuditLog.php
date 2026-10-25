<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'auditable_type', 'auditable_id', 'event', 'module', 'description',
        'old_values', 'new_values',
        'user_id', 'user_name', 'role',
        'branch_id', 'branch_name',
        'ip_address', 'user_agent', 'url', 'method',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    // Human-readable event labels
    public static array $eventLabels = [
        'created'  => 'Tambah Data',
        'updated'  => 'Ubah Data',
        'deleted'  => 'Hapus Data',
        'restored' => 'Pulihkan Data',
        'login'    => 'Login',
        'logout'   => 'Logout',
        'export'   => 'Export',
        'print'    => 'Cetak',
        'approval' => 'Approval',
    ];

    public static array $eventColors = [
        'created'  => '#10b981',
        'updated'  => '#f59e0b',
        'deleted'  => '#ef4444',
        'login'    => '#6366f1',
        'logout'   => '#64748b',
        'export'   => '#0891b2',
        'print'    => '#0891b2',
        'approval' => '#8b5cf6',
        'restored' => '#10b981',
    ];

    public function getEventLabelAttribute(): string
    {
        return self::$eventLabels[$this->event] ?? ucfirst($this->event);
    }

    public function getEventColorAttribute(): string
    {
        return self::$eventColors[$this->event] ?? '#64748b';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
