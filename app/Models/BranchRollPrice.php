<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Harga roll antar cabang per nama film dan kategori kepemilikan cabang pembeli. */
class BranchRollPrice extends Model
{
    protected $fillable = ['ownership_type', 'film_key', 'price', 'unit'];

    protected $casts = ['price' => 'decimal:2'];

    /** Nama film dinormalkan (huruf besar, tanpa spasi tepi) supaya "Black Master" = "BLACK MASTER". */
    public static function keyFor(?string $name): string
    {
        return strtoupper(trim((string) $name));
    }
}
