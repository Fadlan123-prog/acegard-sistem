<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('content_posts')) {
            return;
        }

        Schema::create('content_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('platform', ['tiktok', 'instagram', 'youtube', 'website']);
            $table->string('title');
            $table->date('published_at');
            $table->unsignedBigInteger('views')->default(0);
            $table->decimal('engagement_rate', 5, 2)->nullable();
            $table->unsignedInteger('leads_generated')->default(0);
            $table->boolean('is_fyp')->default(false);
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['branch_id', 'employee_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_posts');
    }
};
