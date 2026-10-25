<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('export_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('international_partner_id')->constrained('international_partners');
            $table->string('order_number')->unique();
            $table->string('currency', 3);
            $table->decimal('exchange_rate', 12, 4);
            $table->unsignedBigInteger('subtotal_foreign');
            $table->unsignedBigInteger('total_revenue_idr');
            $table->enum('status', ['confirmed', 'shipped', 'customs', 'delivered'])->default('confirmed');
            $table->date('order_date');
            $table->date('shipped_date')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('export_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('export_order_id')->constrained()->cascadeOnDelete();
            $table->string('item_name');
            $table->decimal('quantity', 10, 2);
            $table->string('unit');
            $table->unsignedBigInteger('unit_price_foreign');
            $table->timestamps();
        });

        Schema::create('export_order_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('export_order_id')->constrained()->cascadeOnDelete();
            $table->enum('doc_type', ['invoice', 'packing_list', 'customs_declaration', 'other']);
            $table->string('file_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('export_order_documents');
        Schema::dropIfExists('export_order_items');
        Schema::dropIfExists('export_orders');
    }
};
