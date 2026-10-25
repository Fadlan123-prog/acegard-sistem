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
        Schema::create('warranty_claim_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('warranty_claim_id')
                ->constrained('warranty_claims')
                ->cascadeOnDelete();

            $table->enum('item_type', ['car_product', 'building_customer', 'ppf_panel'])->nullable();

            // car film -> simpan customer_product.id
            $table->unsignedBigInteger('item_id')->nullable();

            // ppf -> simpan key panel (hood, door_fl, dsb)
            $table->string('item_key', 80)->nullable();

            $table->string('item_label', 255)->nullable();
            $table->enum('action', ['repair','replace'])->default('replace');
            $table->unsignedInteger('charge_amount')->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranty_claims_items');
    }
};
