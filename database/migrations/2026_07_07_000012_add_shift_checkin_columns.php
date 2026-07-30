<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend attendances with shift assignment, GPS check-in, and calculated minutes
        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('shift_id')->nullable()->after('created_by');
            $table->foreign('shift_id')->references('id')->on('shifts')->nullOnDelete();

            $table->unsignedBigInteger('leave_request_id')->nullable()->after('shift_id');
            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->nullOnDelete();

            $table->timestamp('check_in_at')->nullable()->after('leave_request_id');
            $table->timestamp('check_out_at')->nullable()->after('check_in_at');

            $table->decimal('check_in_lat',  10, 7)->nullable()->after('check_out_at');
            $table->decimal('check_in_lng',  10, 7)->nullable()->after('check_in_lat');
            $table->decimal('check_out_lat', 10, 7)->nullable()->after('check_in_lng');
            $table->decimal('check_out_lng', 10, 7)->nullable()->after('check_out_lat');

            $table->string('check_in_photo')->nullable()->after('check_out_lng');

            $table->unsignedSmallInteger('late_minutes')->default(0)->after('check_in_photo');
            $table->unsignedSmallInteger('overtime_minutes')->default(0)->after('late_minutes');
        });

        // Add mobile_token to employees so the check-in API can authenticate without a session
        Schema::table('employees', function (Blueprint $table) {
            $table->string('mobile_token', 64)->nullable()->unique()->after('ppf_admin_commission');
        });

        // Extend payroll_items with overtime_pay and late_deduction columns
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->integer('overtime_minutes')->default(0)->after('absent_days');
            $table->integer('overtime_pay')->default(0)->after('overtime_minutes');
            $table->integer('late_deduction')->default(0)->after('overtime_pay');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropColumn(['overtime_minutes', 'overtime_pay', 'late_deduction']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('mobile_token');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropForeign(['leave_request_id']);
            $table->dropColumn([
                'shift_id', 'leave_request_id',
                'check_in_at', 'check_out_at',
                'check_in_lat', 'check_in_lng',
                'check_out_lat', 'check_out_lng',
                'check_in_photo', 'late_minutes', 'overtime_minutes',
            ]);
        });
    }
};
