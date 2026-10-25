<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creative_task_deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creative_task_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('version');
            $table->string('file_path');
            $table->text('note')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creative_task_deliverables');
    }
};
