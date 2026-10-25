<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('development_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users');
            $table->string('title');
            $table->text('content');
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('development_reports');
    }
};
