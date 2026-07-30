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
            $table->timestamp('stock_deducted_at')->nullable()->after('commission_released_by');
            $table->unsignedBigInteger('stock_deducted_by')->nullable()->after('stock_deducted_at');
            $table->foreign('stock_deducted_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['stock_deducted_by']);
            $table->dropColumn(['stock_deducted_at', 'stock_deducted_by']);
        });
    }
};
