<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_payment_batches', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->after('id');
            $table->string('requester_notes')->nullable()->after('notes');

            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::table('commission_payment_batches', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn(['employee_id', 'requester_notes']);
        });
    }
};
