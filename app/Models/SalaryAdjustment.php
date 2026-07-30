<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryAdjustment extends Model
{
    protected $fillable = [
        'employee_id',
        'month',
        'uang_motor',
        'uang_makan',
        'potongan_rokok',
        'kasbon',
        'potongan_absen',
        'created_by',
    ];

    protected $casts = [
        'uang_motor'      => 'int',
        'uang_makan'      => 'int',
        'potongan_rokok'  => 'int',
        'kasbon'          => 'int',
        'potongan_absen'  => 'int',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getTotalTambahanAttribute(): int
    {
        return $this->uang_motor + $this->uang_makan;
    }

    public function getTotalPotonganAttribute(): int
    {
        return $this->potongan_rokok + $this->kasbon + $this->potongan_absen;
    }
}
