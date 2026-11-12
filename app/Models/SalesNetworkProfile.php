<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

/**
 * Keanggotaan jaringan referral sales — satu baris per Employee yang berperan
 * sales/marketing. Terpisah dari `employees` (lihat migration) karena field
 * ini cuma relevan untuk sebagian kecil karyawan, bukan seluruhnya.
 */
class SalesNetworkProfile extends Model
{
    use Auditable;

    protected $fillable = [
        'employee_id',
        'referral_code',
        'sponsor_id',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public static array $statusLabels = [
        'pending_approval' => 'Menunggu Approval',
        'active'            => 'Aktif',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function sponsor()
    {
        return $this->belongsTo(Employee::class, 'sponsor_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
