<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = ['category_id', 'name', 'description'];

    public function parts()
    {
        return $this->hasMany(Part::class);
    }

    public function category()
    {
        return $this->belongsTo(CategoryProduct::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }


}
