<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mitra extends Model
{
    protected $fillable = [
        'name',
        'type',
        'pic_name',
        'phone',
        'email',
        'address',
        'city',
        'status',
        'notes',
    ];

    public function invoices()
    {
        return $this->hasMany(MitraInvoice::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'mitra'         => 'Mitra',
            'non_franchise' => 'Non-Franchise',
            default         => '-',
        };
    }
}
