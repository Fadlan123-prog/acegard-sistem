<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_roll_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('from_branch_id')->constrained('branches');
            $table->foreignId('to_branch_id')->constrained('branches');
            // Kategori cabang pembeli saat invoice dibuat (mitra = franchise / non_franchise), mengikuti mitras.type.
            $table->string('buyer_ownership_type', 30)->nullable();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            // sent: roll keluar dari stok penjual, menunggu diterima pembeli
            $table->enum('delivery_status', ['sent', 'received', 'cancelled'])->default('sent');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['from_branch_id', 'delivery_status']);
            $table->index(['to_branch_id', 'delivery_status']);
        });

        Schema::create('branch_roll_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_roll_invoice_id')->constrained('branch_roll_invoices')->cascadeOnDelete();
            $table->unsignedBigInteger('film_stock_pool_id');
            $table->string('item_name');
            $table->unsignedInteger('qty');
            $table->string('unit', 50)->default('roll');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->timestamps();
        });

        Schema::create('branch_roll_invoice_rolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_roll_invoice_item_id')->constrained('branch_roll_invoice_items')->cascadeOnDelete();
            $table->unsignedBigInteger('film_roll_entry_id');
            $table->string('roll_code');
            $table->decimal('length_meter', 8, 2);
            $table->timestamps();

            $table->index('film_roll_entry_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_roll_invoice_rolls');
        Schema::dropIfExists('branch_roll_invoice_items');
        Schema::dropIfExists('branch_roll_invoices');
            }
};
