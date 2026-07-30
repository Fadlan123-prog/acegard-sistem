<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('film_complaints', function (Blueprint $table) {
            $table->id();

            $table->string('complaint_number')->unique();

            $table->unsignedBigInteger('branch_id');

            // Complain mobil
            $table->unsignedBigInteger('customer_id')->nullable();

            // Complain gedung
            $table->unsignedBigInteger('customer_building_id')->nullable();

            $table->enum('complaint_type', ['car', 'building']);

            $table->date('complaint_date');

            $table->enum('status', [
                'open',
                'process',
                'completed',
                'cancelled'
            ])->default('completed');

            $table->string('reason')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->index(['branch_id']);
            $table->index(['customer_id']);
            $table->index(['customer_building_id']);
            $table->index(['complaint_type']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('film_complaints');
    }
};
