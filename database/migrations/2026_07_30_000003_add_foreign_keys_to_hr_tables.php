<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('generated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            $table->foreign('payroll_id')->references('id')->on('payrolls')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->restrictOnDelete();
        });

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->foreign('attendance_id')->references('id')->on('attendances')->nullOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('changed_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('commission_corrections', function (Blueprint $table) {
            $table->foreign('commission_id')->references('id')->on('commissions')->cascadeOnDelete();
            $table->foreign('old_employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('new_employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('changed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('commission_corrections', function (Blueprint $table) {
            $table->dropForeign(['commission_id']);
            $table->dropForeign(['old_employee_id']);
            $table->dropForeign(['new_employee_id']);
            $table->dropForeign(['changed_by']);
        });

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropForeign(['attendance_id']);
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['changed_by']);
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropForeign(['payroll_id']);
            $table->dropForeign(['employee_id']);
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['generated_by']);
            $table->dropForeign(['approved_by']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['created_by']);
        });
    }
};
