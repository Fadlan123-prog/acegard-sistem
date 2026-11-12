<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tarif komisi per kategori produk yang bisa diatur dari Manajemen Produk (menggantikan edit config/commission.php).
        // scope: car | building. rate_key: marketing_fullset, marketing_dsp, marketing_skkb (mobil);
        // technician_per_meter, marketing_per_meter (gedung). Bila tidak ada baris, dipakai nilai bawaan di config.
        Schema::create('commission_rates', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 20);
            $table->unsignedBigInteger('category_id');
            $table->string('rate_key', 40);
            $table->unsignedBigInteger('amount')->default(0);
            $table->foreignId('updated_by')->nullable()->constrained('users', 'id', 'fk_commission_rates_user')->nullOnDelete();
            $table->timestamps();

            $table->unique(['scope', 'category_id', 'rate_key'], 'uniq_commission_rate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_rates');
    }
};
