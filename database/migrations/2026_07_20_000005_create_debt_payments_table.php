<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debt_payments', function (Blueprint $table) {
            $table->id();
            $table->morphs('payable');                 // payable_type + payable_id (Payable or Receivable)
            $table->unsignedBigInteger('amount');
            $table->date('payment_date');
            $table->string('payment_method', 30)->default('transfer'); // cash, transfer, qris
            $table->string('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
    }
};
