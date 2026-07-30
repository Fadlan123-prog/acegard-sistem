<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('export_requests')) return;

        Schema::create('export_requests', function (Blueprint $table) {
            $table->id();
            $table->string('type');                    // payroll_generate|commission_excel|attendance_excel|attendance_pdf
            $table->json('params');                    // all inputs needed to reproduce the job
            $table->string('status')->default('pending'); // pending|processing|done|failed
            $table->string('file_path')->nullable();   // storage-relative path for file exports
            $table->string('file_name')->nullable();   // user-facing download filename
            $table->unsignedBigInteger('result_id')->nullable(); // payroll.id after payroll_generate
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('export_requests');
    }
};
