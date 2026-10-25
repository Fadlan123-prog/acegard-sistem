<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('film_stock_transfer_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('film_stock_transfer_id');
            $table->unsignedBigInteger('film_roll_entry_id');
            $table->unsignedBigInteger('film_stock_pool_id');

            $table->string('roll_code');

            $table->string('film_type')->nullable();
            $table->string('darkness_code')->nullable();

            $table->decimal('width_meter', 8, 2)->default(1.50);
            $table->decimal('length_meter', 8, 2)->default(30.00);
            $table->decimal('area_meter', 10, 2)->default(45.00);

            $table->timestamps();

            $table->index(['film_stock_transfer_id']);
            $table->index(['film_roll_entry_id']);
            $table->index(['roll_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('film_stock_transfer_items');
    }
};
