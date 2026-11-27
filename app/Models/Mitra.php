<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mitra extends Model
{
    /** Satu kosakata kategori franchise/non-franchise, dipakai juga oleh Branch::ownership_type. */
    public const TYPES = [
        'mitra'         => 'Franchise',
        'non_franchise' => 'Non-Franchise',
    ];

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
        return self::TYPES[$this->type] ?? '-';
    }
}
