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
        Schema::create('petty_cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['in', 'out']);
            $table->date('date');
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('destination')->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->bigInteger('fuel_cost')->default(0);
            $table->bigInteger('amount');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petty_cash_transactions');
    }
};
