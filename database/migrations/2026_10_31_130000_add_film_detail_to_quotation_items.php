<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rincian kaca film mobil per baris quotation: bagian kaca, tipe film, persentase kegelapan.
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->json('part_ids')->nullable()->after('specifications');
            $table->unsignedBigInteger('film_category_id')->nullable()->after('part_ids');
            $table->unsignedBigInteger('film_product_id')->nullable()->after('film_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn(['part_ids', 'film_category_id', 'film_product_id']);
        });
    }
};
