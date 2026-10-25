<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('amount');
            // pending, eligible, paid, cancelled

            $table->timestamp('eligible_at')->nullable()->after('status');
            $table->timestamp('paid_at')->nullable()->after('eligible_at');

            $table->unsignedBigInteger('paid_by')->nullable()->after('paid_at');
            $table->unsignedBigInteger('commission_payment_batch_id')->nullable()->after('paid_by');

            $table->text('payment_notes')->nullable()->after('commission_payment_batch_id');

            $table->index('status');
            $table->index('eligible_at');
            $table->index('paid_at');
            $table->index('commission_payment_batch_id');
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'eligible_at',
                'paid_at',
                'paid_by',
                'commission_payment_batch_id',
                'payment_notes',
            ]);
        });
    }
};
