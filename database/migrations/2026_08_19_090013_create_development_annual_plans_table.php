<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('development_annual_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users');
            $table->unsignedSmallInteger('year');
            $table->text('summary');
            $table->timestamps();

            $table->unique('year');
        });

        Schema::create('development_annual_plan_initiatives', function (Blueprint $table) {
            $table->id();
            // Short column name deliberately: 'development_annual_plan_id' pushes
            // the auto-generated FK constraint name over MySQL's 64-char identifier
            // limit (caught by actually running this migration, not just php -l).
            $table->foreignId('plan_id')->constrained('development_annual_plans')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('target_date')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'done', 'cancelled'])->default('planned');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('development_annual_plan_initiatives');
        Schema::dropIfExists('development_annual_plans');
    }
};
