<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('international_partner_id')->constrained('international_partners');
            $table->string('order_number')->unique();
            $table->string('currency', 3);
            $table->decimal('exchange_rate', 12, 4);
            $table->unsignedBigInteger('subtotal_foreign');
            $table->unsignedBigInteger('duty_cost')->default(0);
            $table->unsignedBigInteger('tax_cost')->default(0);
            $table->unsignedBigInteger('freight_cost')->default(0);
            $table->unsignedBigInteger('insurance_cost')->default(0);
            $table->unsignedBigInteger('total_cost_idr');
            $table->enum('status', ['ordered', 'shipped', 'customs', 'received'])->default('ordered');
            $table->date('order_date');
            $table->date('estimated_arrival')->nullable();
            $table->date('received_date')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('import_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_order_id')->constrained()->cascadeOnDelete();
            $table->string('item_name');
            $table->decimal('quantity', 10, 2);
            $table->string('unit');
            $table->unsignedBigInteger('unit_price_foreign');
            $table->timestamps();
        });

        Schema::create('import_order_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_order_id')->constrained()->cascadeOnDelete();
            $table->enum('doc_type', ['invoice', 'packing_list', 'bill_of_lading', 'certificate_of_origin', 'other']);
            $table->string('file_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_order_documents');
        Schema::dropIfExists('import_order_items');
        Schema::dropIfExists('import_orders');
    }
};
