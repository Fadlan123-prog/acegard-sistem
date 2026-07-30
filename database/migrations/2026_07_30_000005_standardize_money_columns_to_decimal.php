<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->change();
            $table->decimal('discount', 15, 2)->default(0)->change();
            $table->decimal('downpayment', 15, 2)->default(0)->change();
            $table->decimal('total_price', 15, 2)->change();
            $table->decimal('remaining_payment', 15, 2)->change();
        });

        Schema::table('invoice_building', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->change();
            $table->decimal('discount', 15, 2)->change();
            $table->decimal('down_payment', 15, 2)->change();
            $table->decimal('total_price', 15, 2)->change();
            $table->decimal('remaining_payment', 15, 2)->change();
        });

        Schema::table('ppf_invoices', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->default(0)->change();
            $table->decimal('discount', 15, 2)->default(0)->change();
            $table->decimal('downpayment', 15, 2)->default(0)->change();
            $table->decimal('total_price', 15, 2)->default(0)->change();
            $table->decimal('remaining_payment', 15, 2)->default(0)->change();
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            $table->decimal('monthly_salary', 15, 2)->default(0)->change();
            $table->decimal('prorate_salary', 15, 2)->default(0)->change();
            $table->decimal('transport_allowance', 15, 2)->default(0)->change();
            $table->decimal('meal_allowance', 15, 2)->default(0)->change();
            $table->decimal('main_deduction', 15, 2)->default(0)->change();
            $table->decimal('cash_advance', 15, 2)->default(0)->change();
            $table->decimal('attendance_deduction', 15, 2)->default(0)->change();
            $table->decimal('other_deduction', 15, 2)->default(0)->change();
            $table->decimal('total_salary', 15, 2)->default(0)->change();
            $table->decimal('rounded_salary', 15, 2)->default(0)->change();
        });

        Schema::table('petty_cash_transactions', function (Blueprint $table) {
            $table->decimal('fuel_cost', 15, 2)->default(0)->change();
            $table->decimal('amount', 15, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('price')->change();
            $table->integer('discount')->default(0)->change();
            $table->integer('downpayment')->default(0)->change();
            $table->integer('total_price')->change();
            $table->integer('remaining_payment')->change();
        });

        Schema::table('invoice_building', function (Blueprint $table) {
            $table->integer('price')->change();
            $table->integer('discount')->change();
            $table->integer('down_payment')->change();
            $table->integer('total_price')->change();
            $table->integer('remaining_payment')->change();
        });

        Schema::table('ppf_invoices', function (Blueprint $table) {
            $table->unsignedInteger('price')->default(0)->change();
            $table->unsignedInteger('discount')->default(0)->change();
            $table->unsignedInteger('downpayment')->default(0)->change();
            $table->unsignedInteger('total_price')->default(0)->change();
            $table->unsignedInteger('remaining_payment')->default(0)->change();
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->unsignedInteger('amount')->change();
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            $table->integer('monthly_salary')->default(0)->change();
            $table->integer('prorate_salary')->default(0)->change();
            $table->integer('transport_allowance')->default(0)->change();
            $table->integer('meal_allowance')->default(0)->change();
            $table->integer('main_deduction')->default(0)->change();
            $table->integer('cash_advance')->default(0)->change();
            $table->integer('attendance_deduction')->default(0)->change();
            $table->integer('other_deduction')->default(0)->change();
            $table->integer('total_salary')->default(0)->change();
            $table->integer('rounded_salary')->default(0)->change();
        });

        Schema::table('petty_cash_transactions', function (Blueprint $table) {
            $table->bigInteger('fuel_cost')->default(0)->change();
            $table->bigInteger('amount')->change();
        });
    }
};
