<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Phase 3 skeleton — approval workflow for POs above a configured threshold.
// Controller and views are not yet implemented; this migration creates the table
// so the schema is in place for the next phase.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_approvals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_id')
                  ->constrained('purchases')
                  ->cascadeOnDelete();

            $table->foreignId('requested_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->decimal('threshold_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['purchase_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_approvals');
    }
};
