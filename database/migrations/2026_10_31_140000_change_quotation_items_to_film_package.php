<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Paket kaca film mobil per baris quotation: daftar [{part_id, category_id, product_id}] dengan satu harga,
     * plus kendaraan per paket (hanya tampilan dokumen). Menggantikan kolom part_ids/film_category_id/film_product_id
     * dari migrasi sebelumnya (aman dijalankan di database yang sudah / belum menjalankannya).
     */
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            if (! Schema::hasColumn('quotation_items', 'film_lines')) {
                $table->json('film_lines')->nullable()->after('specifications');
            }
            if (! Schema::hasColumn('quotation_items', 'vehicle_label')) {
                $table->string('vehicle_label', 120)->nullable()->after('film_lines');
            }
            if (! Schema::hasColumn('quotation_items', 'vehicle_plate')) {
                $table->string('vehicle_plate', 20)->nullable()->after('vehicle_label');
            }
        });

        foreach (['part_ids', 'film_category_id', 'film_product_id'] as $old) {
            if (Schema::hasColumn('quotation_items', $old)) {
                Schema::table('quotation_items', fn (Blueprint $t) => $t->dropColumn($old));
            }
        }
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            foreach (['film_lines', 'vehicle_label', 'vehicle_plate'] as $col) {
                if (Schema::hasColumn('quotation_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
