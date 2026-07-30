<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_receipts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_id')
                  ->constrained('purchases')
                  ->cascadeOnDelete();

            $table->foreignId('received_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->string('receipt_number')->unique();
            $table->date('receipt_date');

            // partial = some items not yet fully received, complete = all matched
            $table->enum('status', ['partial', 'complete'])->default('partial');

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['purchase_id', 'created_at']);
        });

        Schema::create('purchase_receipt_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('receipt_id')
                  ->constrained('purchase_receipts')
                  ->cascadeOnDelete();

            $table->foreignId('purchase_item_id')
                  ->constrained('purchase_items')
                  ->cascadeOnDelete();

            $table->decimal('qty_received', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['receipt_id']);
            $table->index(['purchase_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_receipt_items');
        Schema::dropIfExists('purchase_receipts');
    }
};
