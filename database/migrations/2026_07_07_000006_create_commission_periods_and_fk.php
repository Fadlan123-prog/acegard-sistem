<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_periods', function (Blueprint $table) {
            $table->id();
            $table->date('period_start')->unique(); // e.g. 2026-06-16
            $table->date('period_end');             // e.g. 2026-07-15
            $table->timestamps();
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->unsignedBigInteger('commission_period_id')->nullable()->after('id');
            $table->foreign('commission_period_id')
                  ->references('id')->on('commission_periods')
                  ->nullOnDelete();
            $table->index('commission_period_id');
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropForeign(['commission_period_id']);
            $table->dropIndex(['commission_period_id']);
            $table->dropColumn('commission_period_id');
        });

        Schema::dropIfExists('commission_periods');
    }
};
