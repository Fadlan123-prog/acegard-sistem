<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilmComplaint extends Model
{
    protected $fillable = [
        'complaint_number',
        'branch_id',
        'customer_id',
        'customer_building_id',
        'complaint_type',
        'complaint_date',
        'status',
        'reason',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'complaint_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(FilmComplaintItem::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerBuilding()
    {
        return $this->belongsTo(CustomerBuilding::class, 'customer_building_id');
    }

    public function stockUsages()
    {
        return $this->hasMany(FilmStockUsage::class, 'film_complaint_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
