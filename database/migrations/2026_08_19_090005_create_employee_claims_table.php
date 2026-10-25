<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained();
            $table->enum('type', ['pembelian', 'bensin', 'kesehatan']);
            $table->unsignedBigInteger('amount');
            $table->text('description')->nullable();
            $table->string('proof_path')->nullable();
            $table->enum('status', ['pending_approval', 'approved', 'rejected', 'paid'])->default('pending_approval');
            $table->foreignId('petty_cash_transaction_id')->nullable()->constrained('petty_cash_transactions')->nullOnDelete();
            $table->foreignId('disbursed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_claims');
    }
};
