<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add BPJS, NPWP, and HR metadata to employees
        Schema::table('employees', function (Blueprint $table) {
            $table->string('npwp', 20)->nullable()->after('phone_number');
            $table->date('hire_date')->nullable()->after('is_active');
            $table->enum('employment_status', ['permanent', 'contract', 'probation'])
                  ->default('permanent')->after('hire_date');

            // BPJS Ketenagakerjaan
            $table->string('bpjs_ketenagakerjaan_no', 30)->nullable()->after('employment_status');
            $table->boolean('bpjs_ketenagakerjaan_enrolled')->default(false)->after('bpjs_ketenagakerjaan_no');
            $table->tinyInteger('jkk_risk_group')->unsigned()->default(1)->after('bpjs_ketenagakerjaan_enrolled');

            // BPJS Kesehatan
            $table->string('bpjs_kesehatan_no', 30)->nullable()->after('jkk_risk_group');
            $table->boolean('bpjs_kesehatan_enrolled')->default(false)->after('bpjs_kesehatan_no');
        });

        // Add BPJS line items to payroll_items
        Schema::table('payroll_items', function (Blueprint $table) {
            // Employee deductions
            $table->integer('bpjs_jht_employee')->default(0)->after('late_deduction');
            $table->integer('bpjs_jp_employee')->default(0)->after('bpjs_jht_employee');
            $table->integer('bpjs_kes_employee')->default(0)->after('bpjs_jp_employee');
            $table->integer('bpjs_total_employee')->default(0)->after('bpjs_kes_employee');

            // Employer contributions (informational, not deducted from take-home)
            $table->integer('bpjs_jkk_employer')->default(0)->after('bpjs_total_employee');
            $table->integer('bpjs_jkm_employer')->default(0)->after('bpjs_jkk_employer');
            $table->integer('bpjs_jht_employer')->default(0)->after('bpjs_jkm_employer');
            $table->integer('bpjs_jp_employer')->default(0)->after('bpjs_jht_employer');
            $table->integer('bpjs_kes_employer')->default(0)->after('bpjs_jp_employer');
            $table->integer('bpjs_total_employer')->default(0)->after('bpjs_kes_employer');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropColumn([
                'bpjs_jht_employee', 'bpjs_jp_employee', 'bpjs_kes_employee', 'bpjs_total_employee',
                'bpjs_jkk_employer', 'bpjs_jkm_employer', 'bpjs_jht_employer',
                'bpjs_jp_employer', 'bpjs_kes_employer', 'bpjs_total_employer',
            ]);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'npwp', 'hire_date', 'employment_status',
                'bpjs_ketenagakerjaan_no', 'bpjs_ketenagakerjaan_enrolled', 'jkk_risk_group',
                'bpjs_kesehatan_no', 'bpjs_kesehatan_enrolled',
            ]);
        });
    }
};
