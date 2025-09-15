<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('payment_method'); // cash or credit
            $table->integer('price');
            $table->integer('discount')->default(0);
            $table->integer('downpayment')->default(0);
            $table->integer('total_price');
            $table->integer('remaining_payment');
            $table->string('status')->default('unpaid');
            $table->string('invoice_number')->unique();
            $table->dateTime('invoice_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
