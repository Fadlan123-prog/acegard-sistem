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
        Schema::table('invoices', function (Blueprint $table) {
            // Step 2: pelunasan
            $table->timestamp('pelunasan_at')->nullable()->after('paid_at');
            $table->unsignedBigInteger('pelunasan_by')->nullable()->after('pelunasan_at');
            $table->foreign('pelunasan_by')->references('id')->on('users')->nullOnDelete();

            // Step 3: komisi
            $table->timestamp('commission_released_at')->nullable()->after('pelunasan_at');
            $table->unsignedBigInteger('commission_released_by')->nullable()->after('commission_released_at');
            $table->foreign('commission_released_by')->references('id')->on('users')->nullOnDelete();
        });

        // Backfill: invoice yang sudah paid dianggap sudah step 2
        \DB::statement("UPDATE invoices SET pelunasan_at = paid_at WHERE status = 'paid' AND paid_at IS NOT NULL");

        // Backfill: invoice yang commission-nya sudah ada (via customer) dianggap sudah step 3
        \DB::statement("
            UPDATE invoices i
            SET commission_released_at = i.created_at,
                commission_released_by = NULL
            WHERE EXISTS (
                SELECT 1 FROM commissions c
                WHERE c.customer_id = i.customer_id
                  AND c.invoice_id IS NULL
            )
        ");
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['pelunasan_by']);
            $table->dropForeign(['commission_released_by']);
            $table->dropColumn(['pelunasan_at', 'pelunasan_by', 'commission_released_at', 'commission_released_by']);
        });
    }
};
