<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id', 'type', 'document_name',
        'file_path', 'expires_at', 'uploaded_by', 'notes',
    ];

    protected $casts = [
        'expires_at' => 'date',
    ];

    public static array $typeLabels = [
        'ktp'                  => 'KTP',
        'npwp'                 => 'NPWP',
        'contract'             => 'Kontrak Kerja',
        'bpjs_kesehatan'       => 'BPJS Kesehatan',
        'bpjs_ketenagakerjaan' => 'BPJS Ketenagakerjaan',
        'photo'                => 'Foto Karyawan',
        'other'                => 'Lainnya',
    ];

    public function employee()  { return $this->belongsTo(Employee::class); }
    public function uploader()  { return $this->belongsTo(User::class, 'uploaded_by'); }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isBeforeToday();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expires_at
            && !$this->isExpired()
            && $this->expires_at->diffInDays(now()) <= $days;
    }
}
