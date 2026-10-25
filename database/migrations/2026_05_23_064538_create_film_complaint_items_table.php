<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('film_complaint_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('film_complaint_id');

            /*
            |--------------------------------------------------------------------------
            | Produk Mobil
            |--------------------------------------------------------------------------
            */
            $table->unsignedBigInteger('category_product_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('part_id')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Detail Pergantian Mobil
            |--------------------------------------------------------------------------
            |
            | replacement_side:
            | - not_applicable = untuk windshield, rear window, panoramic
            | - left = kiri
            | - right = kanan
            | - both = kanan dan kiri
            |
            | replacement_scope:
            | - full = full kaca
            | - partial = sebagian
            | - manual = input meter manual
            |
            */
            $table->enum('replacement_side', [
                'not_applicable',
                'left',
                'right',
                'both'
            ])->default('not_applicable');

            $table->enum('replacement_scope', [
                'full',
                'partial',
                'manual'
            ])->default('full');

            $table->decimal('coverage_percent', 5, 2)->default(100);

            /*
            |--------------------------------------------------------------------------
            | Produk Gedung
            |--------------------------------------------------------------------------
            */
            $table->unsignedBigInteger('category_product_building_id')->nullable();
            $table->unsignedBigInteger('product_building_id')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Pemakaian
            |--------------------------------------------------------------------------
            */
            $table->decimal('area_meter', 12, 2)->default(0);
            $table->decimal('used_length_meter', 12, 2)->default(0);
            $table->decimal('waste_percent', 5, 2)->default(0);

            $table->enum('usage_source', ['estimate', 'manual'])
                ->default('manual');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['film_complaint_id']);
            $table->index(['product_id']);
            $table->index(['product_building_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('film_complaint_items');
    }
};
