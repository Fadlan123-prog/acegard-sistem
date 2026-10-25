<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class Employee extends Model
{
    use Auditable, SoftDeletes, HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'branch_id',
        'name',
        'phone_number',
        'npwp',
        'job_position',
        'monthly_salary',
        'is_active',
        'hire_date',
        'birth_date',
        'employment_status',
        'meal_allowance_enabled',
        'meal_allowance_rate',
        'ppf_admin_commission',
        'commission_mode',
        'commission_percentage',
        'mobile_token',
        'bpjs_ketenagakerjaan_no',
        'bpjs_ketenagakerjaan_enrolled',
        'jkk_risk_group',
        'bpjs_kesehatan_no',
        'bpjs_kesehatan_enrolled',
    ];

    protected $casts = [
        'is_active'                     => 'boolean',
        'ppf_admin_commission'         => 'boolean',
        'commission_percentage'         => 'float',
        'bpjs_ketenagakerjaan_enrolled' => 'boolean',
        'bpjs_kesehatan_enrolled'       => 'boolean',
        'hire_date'                     => 'date',
        'birth_date'                    => 'date',
        'jkk_risk_group'                => 'integer',
    ];

    public static array $commissionModeLabels = [
        'default'    => 'Sistem Saat Ini',
        'percentage' => 'Persentase Kustom',
        'none'       => 'Tidak Dapat Komisi',
    ];

    public function commissions()
    {
        return $this->hasMany(Commission::class, 'employees_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function overrideTriggers()
    {
        return $this->hasMany(\App\Models\CommissionOverrideTrigger::class, 'override_employee_id');
    }

    public function marketingTriggers()
    {
        return $this->hasMany(\App\Models\CommissionOverrideTrigger::class, 'trigger_marketing_id');
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function histories()
    {
        return $this->hasMany(EmployeeHistory::class)->orderByDesc('effective_date');
    }

    public function healthBalances()
    {
        return $this->hasMany(EmployeeHealthBalance::class);
    }

    /**
     * Karyawan aktif yang ulang tahunnya jatuh dalam N hari ke depan (termasuk
     * hari ini), diurutkan dari yang paling dekat. birth_date berulang tiap
     * tahun jadi dicocokkan lewat bulan+tanggal saja (tahun lahir diabaikan),
     * dihitung di PHP (bukan SQL mentah) supaya aman dari edge-case 29 Februari
     * dan pergantian tahun (Des → Jan) tanpa perlu query tanggal yang rapuh.
     *
     * $branchId null = semua cabang (dipakai superadmin/owner/cs yang memang
     * tidak discope per cabang di dashboard masing-masing).
     */
    public static function upcomingBirthdays(?int $branchId = null, int $days = 7): \Illuminate\Support\Collection
    {
        $today = now()->startOfDay();

        return static::query()
            ->where('is_active', true)
            ->whereNotNull('birth_date')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get()
            ->map(function (self $employee) use ($today) {
                $next = $employee->birth_date->copy()->year($today->year)->startOfDay();
                if ($next->lt($today)) {
                    $next = $next->copy()->addYear();
                }
                $employee->next_birthday_date = $next;
                // diffInDays() returns float — cast to int so strict (===) comparisons
                // against 0/1 elsewhere (badge logic, "today" row highlight, confetti
                // trigger) actually match instead of silently failing (0.0 !== 0).
                $employee->days_until_birthday = (int) $today->diffInDays($next);
                return $employee;
            })
            ->filter(fn (self $employee) => $employee->days_until_birthday <= $days)
            ->sortBy('days_until_birthday')
            ->values();
    }
}
