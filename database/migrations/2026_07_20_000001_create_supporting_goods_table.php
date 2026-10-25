<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supporting_goods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category', 30); // chemical, cutter, squeegee, heat_gun, tools, other
            $table->string('unit', 30)->default('pcs');
            $table->decimal('current_stock', 10, 2)->default(0);
            $table->decimal('minimum_stock', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supporting_goods');
    }
};
