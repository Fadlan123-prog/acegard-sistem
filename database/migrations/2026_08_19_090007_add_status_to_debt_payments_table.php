<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('debt_payments', function (Blueprint $table) {
            // Existing rows predate the approval gate and were already applied
            // to paid_amount, so they default to 'approved' to stay correct.
            $table->enum('status', ['pending_approval', 'approved', 'rejected'])
                ->default('approved')
                ->after('recorded_by');
        });
    }

    public function down(): void
    {
        Schema::table('debt_payments', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
