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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('branch_id');
            $table->string('month'); // format: 2026-05
            $table->integer('working_days')->default(26);

            $table->enum('status', ['draft', 'approved', 'paid', 'cancelled'])
                ->default('draft');

            $table->unsignedBigInteger('generated_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['branch_id', 'month']);
            $table->index(['branch_id']);
            $table->index(['month']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
