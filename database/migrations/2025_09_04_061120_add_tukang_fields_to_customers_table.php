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
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('tukang_id')
                  ->nullable()
                  ->after('install_type')
                  ->constrained('employees')
                  ->nullOnDelete();

            $table->foreignId('kenek_id')
                  ->nullable()
                  ->after('tukang_id')
                  ->constrained('employees')
                  ->nullOnDelete();

            $table->foreignId('marketing_id')
                  ->nullable()
                  ->after('kenek_id')
                  ->constrained('employees')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['tukang_id']);
            $table->dropForeign(['kenek_id']);
            $table->dropForeign(['marketing_id']);

        });
    }
};
