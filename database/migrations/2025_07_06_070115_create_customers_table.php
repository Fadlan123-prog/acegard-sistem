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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->string('wsn')->unique();
            $table->decimal('card_number', 12, 0)->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('address');
            $table->string('dealer_name')->nullable();
            $table->string('applicator')->nullable();
            $table->string('city');
            $table->string('country')->default('Indonesia');
            $table->string('vehicle_brand');
            $table->string('vehicle_model');
            $table->string('plat_number');
            $table->year('vehicle_year');
            $table->date('warantee_start')->nullable();
            $table->date('warantee_end')->nullable();
            $table->integer('warantee_duration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
