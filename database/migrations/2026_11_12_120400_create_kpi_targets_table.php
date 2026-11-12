<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kpi_targets')) {
            return;
        }

        Schema::create('kpi_targets', function (Blueprint $table) {
            $table->id();
            // Free-text division key (customer_service, konten, teknisi, admin,
            // warehouse, purchasing, keuangan, it, development, strategi,
            // advertising, ...) — mirrors "Kartu KPI per Divisi", not a 1:1 map
            // to Spatie roles.
            $table->string('division');
            $table->string('metric_key');
            $table->string('metric_label');
            $table->decimal('target_value', 12, 2);
            $table->decimal('weight', 5, 2); // percentage, 0-100
            $table->enum('direction', ['higher_better', 'lower_better'])->default('higher_better');
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['division', 'metric_key', 'period_month', 'period_year'], 'kpi_targets_unique_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_targets');
    }
};
