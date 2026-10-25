<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpfProduct extends Model
{
    protected $table = 'ppf_products';

    protected $fillable = [
        'name',
        'brand',
        'series',
        'thickness_microns',
        'finish',
        'default_warranty_years',
        'default_coverage',
        'default_exclusions',
    ];

    public function installations()
    {
        return $this->hasMany(CustomerPpfInstallation::class);
    }
}
