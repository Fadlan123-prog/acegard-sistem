<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupportingGood extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'category',
        'unit',
        'current_stock',
        'minimum_stock',
        'description',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
    ];

    public static array $categories = [
        'chemical'  => 'Chemical',
        'cutter'    => 'Cutter',
        'squeegee'  => 'Squeegee',
        'heat_gun'  => 'Heat Gun',
        'tools'     => 'Tools',
        'other'     => 'Lainnya',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function mutations()
    {
        return $this->hasMany(SupportingGoodMutation::class);
    }

    public function isLow(): bool
    {
        return $this->minimum_stock > 0 && (float) $this->current_stock <= (float) $this->minimum_stock;
    }

    public function isCritical(): bool
    {
        return $this->minimum_stock > 0 && (float) $this->current_stock <= ((float) $this->minimum_stock * 0.5);
    }
}
