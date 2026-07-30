<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();

            // null = global shift available to all branches
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();

            $table->string('name');             // e.g. "Shift Pagi", "Shift Malam"
            $table->time('start_time');         // e.g. "08:00:00"
            $table->time('end_time');           // e.g. "17:00:00"

            // Grace period (minutes) after end_time before overtime is counted
            $table->unsignedSmallInteger('overtime_after_minutes')->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['branch_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
