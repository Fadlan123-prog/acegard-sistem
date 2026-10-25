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
        Schema::create('warranty_claim_invoice_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('warranty_claim_invoice_id');
            $table->string('description', 255);
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedInteger('unit_price')->default(0);
            $table->unsignedInteger('total')->default(0);

            $table->timestamps();

            $table->foreign('warranty_claim_invoice_id', 'wcii_inv_fk')
                ->references('id')->on('warranty_claim_invoices')
                ->onDelete('cascade');

            $table->index('warranty_claim_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranty_claim_invoice_items');
    }
};
