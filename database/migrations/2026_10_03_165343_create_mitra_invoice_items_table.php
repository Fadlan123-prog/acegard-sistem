<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mitra_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitra_invoice_id')->constrained('mitra_invoices')->cascadeOnDelete();
            $table->foreignId('mitra_roll_price_id')->nullable()->constrained('mitra_roll_prices')->nullOnDelete();
            $table->string('item_name');
            $table->decimal('qty', 12, 2);
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mitra_invoice_items');
    }
};
