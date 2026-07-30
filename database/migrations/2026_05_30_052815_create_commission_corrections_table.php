<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_corrections', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('commission_id');
            $table->unsignedBigInteger('old_employee_id')->nullable();
            $table->unsignedBigInteger('new_employee_id')->nullable();

            $table->integer('amount')->default(0);
            $table->text('reason')->nullable();

            $table->unsignedBigInteger('changed_by')->nullable();

            $table->timestamps();

            $table->index('commission_id');
            $table->index('old_employee_id');
            $table->index('new_employee_id');
            $table->index('changed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_corrections');
    }
};
