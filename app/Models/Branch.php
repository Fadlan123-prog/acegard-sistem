<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branches';

    protected $fillable = [
        'name',
        'city',
        'address',
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
}
