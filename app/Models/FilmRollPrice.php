<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Harga jual per roll utuh per film (katalog gedung), menurut kategori pembeli: franchise / non-franchise. */
class FilmRollPrice extends Model
{
    protected $table = 'film_roll_prices';

    protected $fillable = [
        'category_product_building_id',
        'price_franchise',
        'price_non_franchise',
        'unit',
    ];

    public function category()
    {
        return $this->belongsTo(CategoryProductBuilding::class, 'category_product_building_id');
    }

    /** @param string $ownershipType franchise | non_franchise (Branch::OWNERSHIP_TYPES) */
    public function priceFor(string $ownershipType): float
    {
        return (float) ($ownershipType === 'non_franchise' ? $this->price_non_franchise : $this->price_franchise);
    }
}
