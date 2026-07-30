<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('created_by')->nullable();

            $table->date('attendance_date');

            $table->enum('status', [
                'present',
                'off',
                'sick',
                'permission',
                'absent'
            ])->default('present');

            $table->integer('daily_salary')->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['employee_id', 'attendance_date']);
            $table->index(['branch_id', 'attendance_date']);
            $table->index(['employee_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
