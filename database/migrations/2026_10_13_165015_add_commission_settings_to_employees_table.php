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
        Schema::table('employees', function (Blueprint $table) {
            // 'default' = pakai perhitungan komisi normal (fixed rate mobil / persentase PPF
            // yang sudah ada); 'percentage' = timpa dengan commission_percentage × harga invoice;
            // 'none' = komisi tetap tercatat (baris dibuat) tapi nominal dipaksa Rp0.
            $table->enum('commission_mode', ['default', 'percentage', 'none'])
                ->default('default')
                ->after('ppf_admin_commission');

            // Disimpan sebagai pecahan (0.03 = 3%), konsisten dengan commission_override_triggers.rate.
            $table->decimal('commission_percentage', 7, 4)->nullable()->after('commission_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['commission_mode', 'commission_percentage']);
        });
    }
};
