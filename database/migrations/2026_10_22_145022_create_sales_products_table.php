<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Katalog produk yang cuma bisa dijual lewat jaringan sales (tidak dijual
     * bebas di cabang). `category` dibuat generik dari awal (bukan hardcode
     * "mobil" di kode) supaya kategori baru nanti cukup tambah baris lewat UI,
     * tidak perlu migration/deploy ulang.
     */
    public function up(): void
    {
        Schema::create('sales_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category', 50); // 'mobil' untuk fase 1, bebas ditambah nanti
            $table->unsignedInteger('price'); // harga tetap, rupiah
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_products');
    }
};
