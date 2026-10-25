<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * stock_qty di sini adalah counter terpisah yang tidak pernah tersambung ke stok
     * roll sungguhan (FilmStockPool/FilmRollEntry) — selalu 0, menyesatkan. Stok mitra
     * invoice sekarang dicek langsung dari FilmStockPool cabang pusat per persentase.
     */
    public function up(): void
    {
        Schema::table('mitra_roll_prices', function (Blueprint $table) {
            $table->dropColumn('stock_qty');
        });
    }

    public function down(): void
    {
        Schema::table('mitra_roll_prices', function (Blueprint $table) {
            $table->decimal('stock_qty', 12, 2)->default(0);
        });
    }
};
