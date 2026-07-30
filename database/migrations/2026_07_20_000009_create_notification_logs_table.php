<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notification_logs')) {
            return;
        }

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 50);
            $table->string('channel', 20);
            $table->string('recipient')->nullable();
            $table->text('message');
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->text('error')->nullable();
            $table->nullableMorphs('relatable');
            $table->timestamps();

            $table->index(['branch_id', 'type', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
