<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 50);
            $table->integer('days_before')->default(3);
            $table->json('channels')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('email_recipients')->nullable();
            $table->json('whatsapp_numbers')->nullable();
            $table->string('whatsapp_api_url', 500)->nullable();
            $table->string('whatsapp_api_token', 255)->nullable();
            $table->timestamps();
            $table->unique(['branch_id', 'type']);
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 50);
            $table->string('channel', 30);
            $table->string('recipient', 255)->nullable();
            $table->text('message');
            $table->string('status', 20)->default('sent');
            $table->text('error')->nullable();
            $table->nullableMorphs('relatable');
            $table->timestamps();
            $table->index(['branch_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_configs');
    }
};
