<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Paket kaca film mobil per baris quotation: daftar [{part_id, category_id, product_id}]
        // (bagian kaca + tipe film + persentase kegelapan), satu harga untuk seluruh paket.
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->json('film_lines')->nullable()->after('specifications');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn('film_lines');
        });
    }
};
