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
        Schema::create('customer_building', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('wsn');
            $table->integer('card_number');
            $table->string('name');
            $table->string('email')->unique();
            $table->integer('phone_number');
            $table->string('address');
            $table->string('dealer_name');
            $table->string('applicator');
            $table->string('city');
            $table->string('country');
            $table->date('warranty_start');
            $table->date('warranty_end');
            $table->date('warranty_duration');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_building');
    }
};
