<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();

            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();

            $table->enum('event_type', [
                'hired',
                'promoted',
                'transferred',
                'resigned',
                'terminated',
                'salary_change',
                'status_change',
            ]);

            $table->string('from_value')->nullable();
            $table->string('to_value')->nullable();
            $table->date('effective_date');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_histories');
    }
};
