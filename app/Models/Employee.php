<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = ['name','job_position'];

    public function commissions()
    {
        return $this->hasMany(Commission::class, 'employees_id');
    }
}
