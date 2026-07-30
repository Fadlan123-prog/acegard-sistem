<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('film_roll_entries', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('film_stock_pool_id');
            $table->unsignedBigInteger('branch_id');

            $table->string('roll_code')->unique();

            $table->string('film_type')->nullable();
            $table->string('darkness_code')->nullable();

            $table->decimal('width_meter', 8, 2)->default(1.50);
            $table->decimal('length_meter', 8, 2)->default(30.00);
            $table->decimal('area_meter', 10, 2)->default(45.00);

            $table->date('received_date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['film_stock_pool_id']);
            $table->index(['branch_id']);
            $table->index(['roll_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('film_roll_entries');
    }
};
