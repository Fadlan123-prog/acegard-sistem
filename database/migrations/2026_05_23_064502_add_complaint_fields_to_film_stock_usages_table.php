<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('film_stock_usages', function (Blueprint $table) {
            if (!Schema::hasColumn('film_stock_usages', 'film_complaint_id')) {
                $table->unsignedBigInteger('film_complaint_id')->nullable()->after('film_stock_pool_id');
            }

            if (!Schema::hasColumn('film_stock_usages', 'usage_reason')) {
                $table->enum('usage_reason', ['normal', 'complaint', 'replacement'])
                    ->default('normal')
                    ->after('usage_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('film_stock_usages', function (Blueprint $table) {
            if (Schema::hasColumn('film_stock_usages', 'usage_reason')) {
                $table->dropColumn('usage_reason');
            }

            if (Schema::hasColumn('film_stock_usages', 'film_complaint_id')) {
                $table->dropColumn('film_complaint_id');
            }
        });
    }
};
