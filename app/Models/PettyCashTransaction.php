<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PettyCashTransaction extends Model
{
    use Auditable, HasFactory;
    protected $fillable = [
        'branch_id',
        'type',
        'date',
        'employee_id',
        'motor_name',
        'destination',
        'item_name',
        'distance_km',
        'fuel_cost',
        'amount',
        'description',
        'created_by',
    ];

    protected $casts = [
        'date'        => 'date',
        'distance_km' => 'float',
        'fuel_cost'   => 'int',
        'amount'      => 'int',
    ];

    public static function calcFuelCost(float $distanceKm): int
    {
        $raw = ($distanceKm * 425) * 2;
        return (int) (round($raw / 1000) * 1000);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
