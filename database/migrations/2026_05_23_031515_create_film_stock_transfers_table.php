<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('film_stock_transfers', function (Blueprint $table) {
            $table->id();

            $table->string('transfer_number')->unique();

            // Gudang asal / cabang asal
            $table->unsignedBigInteger('from_branch_id');

            // Cabang tujuan
            $table->unsignedBigInteger('to_branch_id');

            $table->date('transfer_date');

            $table->enum('status', [
                'draft',
                'sent',
                'received',
                'cancelled'
            ])->default('draft');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['from_branch_id']);
            $table->index(['to_branch_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('film_stock_transfers');
    }
};
