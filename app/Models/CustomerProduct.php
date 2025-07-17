<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProduct extends Model
{
    protected $table = 'customer_product';


    protected $fillable = [
        'customer_id', 'product_id', 'category_product_id', 'part_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function categoryProduct()
    {
        return $this->belongsTo(CategoryProduct::class);
    }
}
