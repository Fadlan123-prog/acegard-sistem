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
        Schema::create('customer_building_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_building_id')->constrained('customer_building')->cascadeOnDelete();
            $table->foreignId('product_building_id')->constrained('product_building')->cascadeOnDelete();
            $table->foreignId('category_product_building_id')->constrained('category_product_building')->cascadeOnDelete();
            $table->decimal('meters');
            $table->date('warantee_start');
            $table->date('warantee_end');
            $table->integer('warantee_duration');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_building_product');
    }
};
