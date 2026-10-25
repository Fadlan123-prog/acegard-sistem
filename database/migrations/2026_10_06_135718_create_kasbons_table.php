<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kasbons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->text('reason');
            $table->unsignedTinyInteger('installments');
            $table->unsignedBigInteger('installment_amount');
            $table->enum('deduction_source', ['payroll', 'commission', 'both']);
            $table->enum('status', ['pending_approval', 'active', 'rejected', 'cancelled', 'completed'])
                ->default('pending_approval');
            $table->unsignedBigInteger('remaining_amount');
            $table->unsignedTinyInteger('installments_paid')->default(0);
            $table->foreignId('requested_by')->constrained('users');
            $table->timestamps();

            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kasbons');
    }
};
