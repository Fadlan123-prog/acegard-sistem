<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('film_part_usage_estimates', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('part_id');

            // fullset / skkb / dsp / nullable untuk default
            $table->string('install_type')->nullable();

            // Panjang roll dalam meter
            $table->decimal('estimated_length_meter', 8, 2)->default(0);

            // Waste cutting / trimming
            $table->decimal('waste_percent', 5, 2)->default(10);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['part_id']);
            $table->index(['install_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('film_part_usage_estimates');
    }
};
