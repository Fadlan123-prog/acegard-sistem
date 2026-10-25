<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'meal_allowance_enabled')) {
                $table->boolean('meal_allowance_enabled')->default(true)->after('monthly_salary');
            }
            if (!Schema::hasColumn('employees', 'meal_allowance_rate')) {
                $table->integer('meal_allowance_rate')->nullable()->after('meal_allowance_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'meal_allowance_enabled')) {
                $table->dropColumn('meal_allowance_enabled');
            }
            if (Schema::hasColumn('employees', 'meal_allowance_rate')) {
                $table->dropColumn('meal_allowance_rate');
            }
        });
    }
};
