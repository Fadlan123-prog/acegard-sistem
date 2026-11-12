<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            // Tipe film & persentase kegelapan dipilih saat penugasan. Menunjuk ke
            // category_products/products (mobil) atau category_product_building/
            // product_building (gedung) sesuai kategori SO — tanpa FK karena dua tabel.
            $table->unsignedBigInteger('film_category_id')->nullable()->after('kenek_id');
            $table->unsignedBigInteger('film_product_id')->nullable()->after('film_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['film_category_id', 'film_product_id']);
        });
    }
};
