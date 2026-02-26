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
        Schema::create('warranty_claim_invoices', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('warranty_claim_id');
            $table->string('invoice_number', 50)->unique();
            $table->dateTime('invoice_date');

            $table->string('payment_method')->default('transfer');
            $table->unsignedInteger('subtotal')->default(0);
            $table->unsignedInteger('discount')->default(0);
            $table->unsignedInteger('downpayment')->default(0);
            $table->unsignedInteger('total_price')->default(0);
            $table->unsignedInteger('remaining_payment')->default(0);

            $table->enum('status', ['unpaid','paid','void'])->default('unpaid');

            $table->timestamps();

            // FK pendek (hindari error nama constraint kepanjangan)
            $table->foreign('warranty_claim_id', 'wci_claim_fk')
                ->references('id')->on('warranty_claims')
                ->onDelete('cascade');

            $table->index('warranty_claim_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranty_claim_invoices');
    }
};
