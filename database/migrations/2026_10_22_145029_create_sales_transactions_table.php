<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Transaksi produk eksklusif sales — sengaja terpisah total dari
     * customers/invoices (kaca film mobil yang sudah ada), karena produk ini
     * tidak melalui instalasi/alur customer biasa. sales_employee_id wajib
     * diisi (not nullable): semua penjualan produk ini harus lewat sales.
     */
    public function up(): void
    {
        Schema::create('sales_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('sales_employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('sales_product_id')->constrained('sales_products')->restrictOnDelete();

            $table->string('customer_name');
            $table->string('customer_phone', 30)->nullable();

            // Snapshot harga saat transaksi dibuat (price bisa berubah di sales_products nanti).
            $table->unsignedInteger('price');
            $table->unsignedInteger('discount')->default(0);
            $table->unsignedInteger('total_price'); // price - discount
            $table->unsignedInteger('downpayment')->default(0);
            $table->unsignedInteger('remaining_payment')->default(0);

            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('pelunasan_at')->nullable();

            $table->timestamp('commission_released_at')->nullable();
            $table->foreignId('commission_released_by')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index('sales_employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_transactions');
    }
};
