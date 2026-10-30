<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Rincian paket kaca film mobil per baris Sales Order (sama seperti quotation_items). */
    public function up(): void
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->json('film_lines')->nullable()->after('specifications');
            $table->string('vehicle_label', 120)->nullable()->after('film_lines');
            $table->string('vehicle_plate', 20)->nullable()->after('vehicle_label');
        });
    }

    public function down(): void
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropColumn(['film_lines', 'vehicle_label', 'vehicle_plate']);
        });
    }
};
