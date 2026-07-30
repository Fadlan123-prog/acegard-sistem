<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('film_stock_pools', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('branch_id');

            // Untuk produk mobil
            $table->unsignedBigInteger('category_product_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();

            // Untuk produk gedung
            $table->unsignedBigInteger('category_product_building_id')->nullable();
            $table->unsignedBigInteger('product_building_id')->nullable();

            // Diisi otomatis dari nama kategori dan produk
            $table->string('film_type')->nullable();
            $table->string('darkness_code')->nullable();

            // car / building / universal
            $table->enum('usage_for', ['car', 'building', 'universal'])->default('universal');

            // Default roll kaca film: 1.5 meter
            $table->decimal('width_meter', 8, 2)->default(1.50);

            // Stok gabungan
            $table->decimal('total_received_length_meter', 12, 2)->default(0);
            $table->decimal('total_used_length_meter', 12, 2)->default(0);
            $table->decimal('remaining_length_meter', 12, 2)->default(0);
            $table->decimal('remaining_area_meter', 12, 2)->default(0);

            $table->timestamps();

            // Nama index dibuat pendek supaya tidak error di MySQL
            $table->index(['branch_id', 'usage_for'], 'fsp_branch_usage_idx');
            $table->index(['category_product_id', 'product_id'], 'fsp_car_product_idx');
            $table->index(['category_product_building_id', 'product_building_id'], 'fsp_building_product_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('film_stock_pools');
    }
};
