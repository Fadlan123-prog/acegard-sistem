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
            // Kendaraan per paket (satu quotation bisa memuat beberapa mobil); hanya tampilan dokumen.
            $table->string('vehicle_label', 120)->nullable()->after('film_lines');
            $table->string('vehicle_plate', 20)->nullable()->after('vehicle_label');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn(['film_lines', 'vehicle_label', 'vehicle_plate']);
        });
    }
};
