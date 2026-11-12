<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kpi_scores')) {
            return;
        }

        Schema::create('kpi_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kpi_target_id')->constrained()->cascadeOnDelete();
            $table->decimal('actual_value', 12, 2);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'kpi_target_id'], 'kpi_scores_unique_entry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_scores');
    }
};
