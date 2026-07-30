<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('month', 7); // format: YYYY-MM
            $table->unsignedBigInteger('uang_motor')->default(0);
            $table->unsignedBigInteger('uang_makan')->default(0);
            $table->unsignedBigInteger('potongan_rokok')->default(0);
            $table->unsignedBigInteger('kasbon')->default(0);
            $table->unsignedBigInteger('potongan_absen')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_adjustments');
    }
};
