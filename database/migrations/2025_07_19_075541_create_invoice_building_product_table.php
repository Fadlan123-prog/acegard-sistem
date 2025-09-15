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
        Schema::create('invoice_building_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_building_id')->constrained('invoice_building')->onDelete('cascade');
            $table->foreignId('customer_building_product_id')->constrained('customer_building_product')->onDelete('cascade');
            $table->integer('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_building_product');
    }
};
