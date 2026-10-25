<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Daftar harga jual roll kaca film dari pusat ke mitra/non-franchise, per tipe
     * film (category_product_building). Terpisah dari category_product_building
     * sendiri karena tabel itu dipakai untuk data job customer (meter terpasang,
     * garansi) — bukan untuk katalog harga jual roll ke mitra.
     */
    public function up(): void
    {
        Schema::create('mitra_roll_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_product_building_id')
                ->constrained('category_product_building')
                ->restrictOnDelete();
            $table->decimal('price_mitra', 15, 2)->default(0);
            $table->decimal('price_non_franchise', 15, 2)->default(0);
            $table->decimal('stock_qty', 12, 2)->default(0);
            $table->string('unit', 30)->default('roll');
            $table->timestamps();

            $table->unique('category_product_building_id');
        });

        // Backfill harga untuk 4 tipe film yang sudah ada (sesuai daftar harga
        // yang diberikan user). Dicocokkan berdasarkan nama, bukan id hardcoded,
        // supaya aman dijalankan di environment lain (id bisa beda per environment).
        $prices = [
            '4K ALPHA PRO' => ['mitra' => 9_000_000, 'non_franchise' => 15_000_000],
            'NOTCH UV 400' => ['mitra' => 7_000_000, 'non_franchise' => 10_000_000],
            'BLACK MASTER' => ['mitra' => 5_000_000, 'non_franchise' => 8_000_000],
            'FLEX ONE WAY' => ['mitra' => 3_000_000, 'non_franchise' => 6_000_000],
        ];

        foreach ($prices as $name => $price) {
            $categoryId = DB::table('category_product_building')
                ->where('name', $name)
                ->value('id');

            if (!$categoryId) {
                continue;
            }

            DB::table('mitra_roll_prices')->insert([
                'category_product_building_id' => $categoryId,
                'price_mitra'                  => $price['mitra'],
                'price_non_franchise'          => $price['non_franchise'],
                'stock_qty'                    => 0,
                'unit'                         => 'roll',
                'created_at'                   => now(),
                'updated_at'                   => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mitra_roll_prices');
    }
};
