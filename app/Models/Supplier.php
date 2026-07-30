<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'phone_number',
        'email',
        'address',
        'city',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
