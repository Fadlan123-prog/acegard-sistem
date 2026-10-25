<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'daily_salary')) {
                $table->integer('daily_salary')->default(0)->after('job_position');
            }

            if (!Schema::hasColumn('employees', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('daily_salary');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'daily_salary')) {
                $table->dropColumn('daily_salary');
            }

            if (Schema::hasColumn('employees', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
