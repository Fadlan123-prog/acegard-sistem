<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fitur Mitra dihapus: kategori franchise / non-franchise ditentukan di data cabang.
     * Daftar harga roll tetap dipakai (Invoice Roll Antar Cabang), jadi hanya diganti namanya.
     * Tabel mitras, mitra_invoices, dan mitra_invoice_items sengaja tidak dihapus (riwayat invoice).
     */
    public function up(): void
    {
        Schema::rename('mitra_roll_prices', 'film_roll_prices');

        Schema::table('film_roll_prices', function (Blueprint $table) {
            $table->renameColumn('price_mitra', 'price_franchise');
        });
    }

    public function down(): void
    {
        Schema::table('film_roll_prices', function (Blueprint $table) {
            $table->renameColumn('price_franchise', 'price_mitra');
        });

        Schema::rename('film_roll_prices', 'mitra_roll_prices');
    }
};
