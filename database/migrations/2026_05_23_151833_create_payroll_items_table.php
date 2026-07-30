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
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('payroll_id');
            $table->unsignedBigInteger('employee_id');

            $table->string('employee_name');
            $table->string('job_position')->nullable();

            $table->integer('monthly_salary')->default(0);
            $table->decimal('daily_prorate', 15, 2)->default(0);

            $table->integer('present_days')->default(0);
            $table->integer('off_days')->default(0);
            $table->integer('off_quota')->default(0);
            $table->integer('paid_off_days')->default(0);
            $table->integer('excess_off_days')->default(0);
            $table->integer('sick_days')->default(0);
            $table->integer('permission_days')->default(0);
            $table->integer('absent_days')->default(0);

            $table->integer('paid_days')->default(0);
            $table->integer('prorate_salary')->default(0);

            $table->integer('transport_allowance')->default(0); // uang motor
            $table->integer('meal_allowance')->default(0);      // uang makan

            $table->integer('main_deduction')->default(0);      // potongan rokok / potongan pokok
            $table->integer('cash_advance')->default(0);        // kasbon
            $table->integer('attendance_deduction')->default(0);
            $table->integer('other_deduction')->default(0);

            $table->integer('total_salary')->default(0);
            $table->integer('rounded_salary')->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['payroll_id']);
            $table->index(['employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};
