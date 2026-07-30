<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_payment_batches', function (Blueprint $table) {
            $table->id();

            $table->string('batch_number')->unique();
            $table->date('period_from');
            $table->date('period_to');
            $table->date('payment_date');

            $table->string('status')->default('draft');
            // draft, paid, cancelled

            $table->integer('total_amount')->default(0);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('period_from');
            $table->index('period_to');
            $table->index('payment_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_payment_batches');
    }
};
