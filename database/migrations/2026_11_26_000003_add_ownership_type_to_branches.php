<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kategori cabang untuk harga roll antar cabang. Nilainya sama dengan mitras.type
     * (mitra = franchise, non_franchise) supaya satu daftar harga (mitra_roll_prices)
     * dipakai Invoice Mitra dan Invoice Roll Antar Cabang.
     */
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('ownership_type', 30)->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('ownership_type');
        });
    }
};
