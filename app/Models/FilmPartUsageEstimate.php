<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilmPartUsageEstimate extends Model
{
    protected $fillable = [
        'part_id',
        'install_type',
        'estimated_length_meter',
        'waste_percent',
        'notes',
    ];

    protected $casts = [
        'estimated_length_meter' => 'decimal:2',
        'waste_percent' => 'decimal:2',
    ];
}
