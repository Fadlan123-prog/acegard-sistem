<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kasbon_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasbon_id')->constrained()->cascadeOnDelete();
            $table->enum('source', ['payroll', 'commission']);
            $table->foreignId('payroll_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('commission_payment_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('period', 7)->nullable(); // Y-m, hanya relevan untuk source=payroll
            $table->timestamps();

            // Idempotency guards — KasbonDeductionService checks these before
            // deducting again, so a payroll "refresh" or a batch re-approval
            // attempt can never double-charge the same period/batch.
            $table->unique(['kasbon_id', 'source', 'period'], 'uniq_kasbon_payroll_period');
            $table->unique(['kasbon_id', 'commission_payment_batch_id'], 'uniq_kasbon_commission_batch');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kasbon_deductions');
    }
};
