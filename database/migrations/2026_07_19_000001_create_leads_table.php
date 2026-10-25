<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->enum('source', ['walk_in', 'referral', 'social_media', 'website', 'whatsapp', 'cold_call', 'other'])->default('walk_in');
            $table->enum('category', ['car_film', 'building_film', 'ppf'])->default('car_film');
            $table->string('vehicle_brand', 100)->nullable();
            $table->string('vehicle_model', 100)->nullable();
            $table->string('vehicle_year', 10)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['new', 'contacted', 'follow_up', 'quoted', 'won', 'lost'])->default('new');
            $table->text('lost_reason')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('employees')->nullOnDelete();
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamp('quoted_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->string('converted_to_type')->nullable();
            $table->unsignedBigInteger('converted_to_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['call', 'whatsapp', 'visit', 'email', 'note', 'status_change']);
            $table->text('notes');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('leads');
    }
};
