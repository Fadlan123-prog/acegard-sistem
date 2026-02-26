<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppf_invoices', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('customer_ppf_id');
            $table->unsignedBigInteger('customer_ppf_installation_id');

            $table->string('invoice_number', 50)->unique();
            $table->date('invoice_date');

            $table->string('payment_method', 50)->default('cash');

            $table->unsignedInteger('price')->default(0);
            $table->unsignedInteger('discount')->default(0);
            $table->unsignedInteger('downpayment')->default(0);

            $table->unsignedInteger('total_price')->default(0);
            $table->unsignedInteger('remaining_payment')->default(0);

            $table->enum('status', ['unpaid','partial','paid','void'])->default('unpaid');

            $table->text('notes')->nullable();

            $table->timestamps();

            // FK name pendek (aman MySQL)
            $table->foreign('branch_id', 'fk_ppfi_branch')
                ->references('id')->on('branches')->restrictOnDelete();

            $table->foreign('customer_ppf_id', 'fk_ppfi_customer')
                ->references('id')->on('customer_ppf')->cascadeOnDelete();

            $table->foreign('customer_ppf_installation_id', 'fk_ppfi_install')
                ->references('id')->on('customer_ppf_installations')->cascadeOnDelete();

            $table->index(['branch_id','invoice_date'], 'ix_ppfi_branch_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppf_invoices');
    }
};
