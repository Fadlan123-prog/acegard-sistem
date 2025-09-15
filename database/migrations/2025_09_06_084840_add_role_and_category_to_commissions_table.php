<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            // role: 'tukang' | 'kenek' | 'marketing' (nullable biar data lama aman)
            $table->string('role', 32)->nullable()->after('amount');

            // optional: kategori produk untuk komisi marketing (atau bisa juga simpan untuk teknisi/kenek jika mau)
            $table->unsignedBigInteger('category_product_id')->nullable()->after('role');

            // FK opsional (hapus jika belum ada tabel/relasinya)
            $table->foreign('category_product_id')
                  ->references('id')->on('category_products')
                  ->nullOnDelete(); // jika kategori dihapus, set null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            try { $table->dropForeign(['category_product_id']); } catch (\Throwable $e) {}
            $table->dropColumn(['role','category_product_id']);
        });
    }
};
