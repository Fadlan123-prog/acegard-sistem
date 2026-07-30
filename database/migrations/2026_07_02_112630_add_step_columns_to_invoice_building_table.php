<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoice_building', function (Blueprint $table) {
            // Step 2: pelunasan
            $table->timestamp('pelunasan_at')->nullable()->after('updated_at');
            $table->unsignedBigInteger('pelunasan_by')->nullable()->after('pelunasan_at');
            $table->foreign('pelunasan_by')->references('id')->on('users')->nullOnDelete();

            // Stock deduction (terjadi bersamaan dengan pelunasan)
            $table->timestamp('stock_deducted_at')->nullable()->after('pelunasan_by');
            $table->unsignedBigInteger('stock_deducted_by')->nullable()->after('stock_deducted_at');
            $table->foreign('stock_deducted_by')->references('id')->on('users')->nullOnDelete();

            // Waste percent untuk perhitungan stok saat pelunasan
            $table->decimal('waste_percent', 5, 2)->default(10)->after('stock_deducted_by');
        });

        // Backfill: invoice yang sudah paid → anggap sudah pelunasan
        \DB::statement("UPDATE invoice_building SET pelunasan_at = updated_at WHERE status = 'paid'");
    }

    public function down(): void
    {
        Schema::table('invoice_building', function (Blueprint $table) {
            $table->dropForeign(['pelunasan_by']);
            $table->dropForeign(['stock_deducted_by']);
            $table->dropColumn(['pelunasan_at', 'pelunasan_by', 'stock_deducted_at', 'stock_deducted_by', 'waste_percent']);
        });
    }
};
