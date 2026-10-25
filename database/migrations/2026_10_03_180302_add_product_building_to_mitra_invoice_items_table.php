<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Harga roll sudah per-kategori (price_mitra/price_non_franchise di mitra_roll_prices),
     * tapi stok fisik di FilmStockPool/FilmRollEntry dipecah per persentase kegelapan
     * (product_building_id) — jadi invoice item perlu tahu persentase mana yang dijual,
     * bukan cuma kategorinya, supaya bisa dicocokkan ke stock pool yang benar.
     */
    public function up(): void
    {
        Schema::table('mitra_invoice_items', function (Blueprint $table) {
            $table->foreignId('product_building_id')
                ->nullable()
                ->after('mitra_roll_price_id')
                ->constrained('product_building')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mitra_invoice_items', function (Blueprint $table) {
            $table->dropForeign(['product_building_id']);
            $table->dropColumn('product_building_id');
        });
    }
};
