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
        Schema::create('invoice_building', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_building_id')->constrained('customer_building')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->integer('price');
            $table->integer('discount');
            $table->integer('down_payment');
            $table->integer('total_price');
            $table->integer('remaining_payment');
            $table->string('status');
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_building');
    }
};
