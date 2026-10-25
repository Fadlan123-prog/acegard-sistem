<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('film_stock_usages', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('film_stock_pool_id');
            $table->unsignedBigInteger('branch_id');

            // Customer mobil
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('customer_product_id')->nullable();

            // Customer gedung
            $table->unsignedBigInteger('customer_building_id')->nullable();
            $table->unsignedBigInteger('customer_building_product_id')->nullable();

            // Produk mobil
            $table->unsignedBigInteger('category_product_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('part_id')->nullable();

            // Produk gedung
            $table->unsignedBigInteger('category_product_building_id')->nullable();
            $table->unsignedBigInteger('product_building_id')->nullable();

            $table->enum('usage_type', ['car', 'building']);

            $table->string('film_type')->nullable();
            $table->string('darkness_code')->nullable();

            $table->string('install_type')->nullable();

            // Info mobil
            $table->string('vehicle_brand')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->string('plat_number')->nullable();

            // Pemakaian
            $table->decimal('area_meter', 12, 2)->default(0);
            $table->decimal('used_length_meter', 12, 2)->default(0);
            $table->decimal('waste_percent', 5, 2)->default(0);

            $table->decimal('stock_before', 12, 2)->default(0);
            $table->decimal('stock_after', 12, 2)->default(0);

            $table->enum('usage_source', ['estimate', 'manual'])->default('estimate');

            $table->date('usage_date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['film_stock_pool_id']);
            $table->index(['usage_date']);
            $table->index(['usage_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('film_stock_usages');
    }
};
