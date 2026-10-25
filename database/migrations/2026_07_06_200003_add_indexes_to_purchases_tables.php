<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $existing = $this->existingIndexes();

        Schema::table('purchases', function (Blueprint $table) use ($existing) {
            if (!in_array('idx_purchases_branch_date', $existing)) {
                $table->index(['branch_id', 'purchase_date'], 'idx_purchases_branch_date');
            }
            if (!in_array('idx_purchases_supplier', $existing)) {
                $table->index('supplier_id', 'idx_purchases_supplier');
            }
            if (!in_array('idx_purchases_status', $existing)) {
                $table->index('status', 'idx_purchases_status');
            }
        });

        Schema::table('purchase_items', function (Blueprint $table) use ($existing) {
            if (!in_array('idx_purchase_items_purchase', $existing)) {
                $table->index('purchase_id', 'idx_purchase_items_purchase');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_purchases_branch_date');
            $table->dropIndexIfExists('idx_purchases_supplier');
            $table->dropIndexIfExists('idx_purchases_status');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_purchase_items_purchase');
        });
    }

    private function existingIndexes(): array
    {
        $rows = DB::select("
            SELECT DISTINCT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
            AND INDEX_NAME LIKE 'idx_%'
        ");

        return array_column($rows, 'INDEX_NAME');
    }
};
