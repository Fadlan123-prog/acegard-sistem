<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Employee;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'password_changed_at' => 'datetime',
        ];
    }

    /**
     * Akun sales boleh dipakai hanya setelah admin menyetujui: employee aktif
     * DAN profil jaringan berstatus active. Role lain: selalu true (perilaku
     * login mereka tidak diubah).
     */
    public function isSalesAccountActive(): bool
    {
        if (! $this->hasRole('sales')) {
            return true;
        }

        $employee = $this->employee; // SoftDeletes → null bila employee dihapus

        if (! $employee || ! $employee->is_active) {
            return false;
        }

        return SalesNetworkProfile::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Boleh memakai tautan atur-password lewat email? Hanya akun yang SELURUH
     * rolenya ada di config('password_setup.self_service_roles') — akun yang
     * juga admin/owner/dll tidak lolos — dan (untuk sales) sudah disetujui.
     */
    public function canUseEmailPasswordSetup(): bool
    {
        $allowed = (array) config('password_setup.self_service_roles', []);
        $roles = $this->getRoleNames();

        if ($allowed === [] || $roles->isEmpty() || $roles->diff($allowed)->isNotEmpty()) {
            return false;
        }

        return $this->isSalesAccountActive();
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_user', 'user_id', 'branch_id')->withTimestamps();
    }

    /**
     * Kebijakan cabang (keputusan 2026-10-30): superadmin dan owner lintas
     * cabang; peran lain (admin, manager, dll.) hanya cabang di branch_user.
     */
    public function canAccessBranch(?int $branchId): bool
    {
        if ($branchId === null) {
            return false;
        }

        if ($this->hasAnyRole(['superadmin', 'owner'])) {
            return true;
        }

        return $this->branches()->where('branches.id', $branchId)->exists();
    }

    /** @return array<int,int> */
    public function accessibleBranchIds(): array
    {
        return $this->branches()->pluck('branches.id')->map(fn ($id) => (int) $id)->all();
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
