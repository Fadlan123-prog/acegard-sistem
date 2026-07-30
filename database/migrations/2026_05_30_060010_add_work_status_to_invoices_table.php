<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('work_status')->default('pending')->after('status');
            // pending, completed, cancelled

            $table->timestamp('completed_at')->nullable()->after('work_status');
            $table->timestamp('paid_at')->nullable()->after('completed_at');

            $table->index('work_status');
            $table->index('completed_at');
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'work_status',
                'completed_at',
                'paid_at',
            ]);
        });
    }
};
