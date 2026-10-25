<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur "Transfer ke Cabang" dihapus — cabang sekarang selalu input stok roll
 * sendiri lewat Roll Masuk, tidak pernah lagi dikirim/dialokasikan dari pusat.
 * Kedua tabel kosong (0 baris) saat migrasi ini dibuat, jadi aman di-drop.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('film_stock_transfer_items');
        Schema::dropIfExists('film_stock_transfers');
    }

    public function down(): void
    {
        Schema::create('film_stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->unsignedBigInteger('from_branch_id');
            $table->unsignedBigInteger('to_branch_id');
            $table->date('transfer_date');
            $table->enum('status', ['draft', 'sent', 'received', 'cancelled'])->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['from_branch_id']);
            $table->index(['to_branch_id']);
            $table->index(['status']);
        });

        Schema::create('film_stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('film_stock_transfer_id');
            $table->unsignedBigInteger('film_roll_entry_id');
            $table->unsignedBigInteger('film_stock_pool_id');
            $table->string('roll_code');
            $table->string('film_type')->nullable();
            $table->string('darkness_code')->nullable();
            $table->decimal('width_meter', 8, 2)->default(1.50);
            $table->decimal('length_meter', 8, 2)->default(30.00);
            $table->decimal('area_meter', 10, 2)->default(45.00);
            $table->timestamps();

            $table->index(['film_stock_transfer_id']);
            $table->index(['film_roll_entry_id']);
            $table->index(['roll_code']);
        });
    }
};
