<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_targets', function (Blueprint $table) {
            // 'manual' atau kode metrik otomatis (lihat App\Services\KpiMetrics).
            $table->string('source', 60)->default('manual')->after('metric_key');
        });
        Schema::table('kpi_scores', function (Blueprint $table) {
            $table->boolean('is_auto')->default(false)->after('actual_value');
        });
    }

    public function down(): void
    {
        Schema::table('kpi_scores', fn (Blueprint $t) => $t->dropColumn('is_auto'));
        Schema::table('kpi_targets', fn (Blueprint $t) => $t->dropColumn('source'));
    }
};
