<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerPpf extends Model
{
    use HasFactory;

    protected $table = 'customer_ppf';

    protected $fillable = [
        'branch_id','wsn','card_number','name','phone','email','address',
        'city','country','vehicle_brand','vehicle_model','vehicle_year',
        'vehicle_color','vehicle_plate','marketing_id',
    ];


    public function installations()
    {
        return $this->hasMany(CustomerPpfInstallation::class, 'customer_ppf_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function marketing()
    {
        return $this->belongsTo(Employee::class, 'marketing_id');
    }
}

