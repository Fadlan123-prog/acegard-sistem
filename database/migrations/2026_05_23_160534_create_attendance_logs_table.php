<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('attendance_id')->nullable();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('branch_id');

            $table->date('attendance_date');

            $table->string('old_status')->nullable();
            $table->string('new_status');

            $table->text('reason')->nullable();

            $table->unsignedBigInteger('changed_by')->nullable();

            $table->timestamps();

            $table->index(['attendance_id']);
            $table->index(['employee_id']);
            $table->index(['branch_id']);
            $table->index(['attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
