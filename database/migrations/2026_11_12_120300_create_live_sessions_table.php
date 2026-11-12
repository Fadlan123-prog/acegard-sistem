<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('live_sessions')) {
            return;
        }

        Schema::create('live_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('session_date');
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->unsignedInteger('viewers_peak')->default(0);
            $table->unsignedInteger('viewers_avg')->default(0);
            $table->unsignedInteger('chat_count')->default(0);
            $table->unsignedInteger('product_questions')->default(0);
            $table->unsignedInteger('leads_generated')->default(0);
            $table->unsignedInteger('closing_count')->default(0);
            $table->decimal('revenue', 14, 2)->default(0);
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['branch_id', 'employee_id', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_sessions');
    }
};
