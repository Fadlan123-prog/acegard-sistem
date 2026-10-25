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
        Schema::create('ppf_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand');
            $table->string('series')->nullable();
            $table->integer('thickness_microns');
            $table->string('finish', 50);

            $table->unsignedTinyInteger('default_warranty_years')->default(5);

            $table->text('default_coverage')->nullable();
            $table->text('default_exclusions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppf_products');
    }
};
