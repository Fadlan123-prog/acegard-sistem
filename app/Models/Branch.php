<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;

    protected $table = 'branches';

    protected $fillable = [
        'name',
        'code',
        'type',
        'status',
        'city',
        'province',
        'district',
        'postal_code',
        'address',
        'phone',
        'whatsapp',
        'email',
        'opening_time',
        'closing_time',
        'working_days',
        'manager_id',
        'pic_id',
        'opening_date',
        'notes',
    ];

    protected $casts = [
        'working_days' => 'array',
        'opening_date' => 'date',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_user', 'branch_id', 'user_id')->withTimestamps();
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function customerBuildings()
    {
        return $this->hasMany(CustomerBuilding::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function invoiceBuildings()
    {
        return $this->hasMany(InvoiceBuilding::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function pic()
    {
        return $this->belongsTo(Employee::class, 'pic_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'Aktif',
            'inactive' => 'Nonaktif',
            default    => '-',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'head_office'    => 'Head Office',
            'branch'         => 'Cabang',
            'warehouse'      => 'Gudang',
            'service_center' => 'Service Center',
            default          => '-',
        };
    }
}
