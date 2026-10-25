<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('requested_by')->nullable(); // user_id who submitted
            $table->unsignedBigInteger('approved_by')->nullable();  // user_id who approved/rejected

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $table->foreign('requested_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();

            $table->enum('leave_type', ['annual', 'sick', 'permission', 'emergency'])
                  ->default('permission');

            $table->date('from_date');
            $table->date('to_date');
            $table->unsignedSmallInteger('days')->default(1);

            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])
                  ->default('pending');

            $table->text('approval_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['from_date', 'to_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
