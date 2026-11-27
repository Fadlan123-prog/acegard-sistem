<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TYPES = ['franchise', 'non_franchise'];

    /**
     * Harga roll antar cabang: satu daftar per NAMA film (tidak dipisah mobil/gedung),
     * per kategori cabang pembeli (franchise / non-franchise). Kolom id kategori diganti film_key (nama film
     * huruf besar), karena roll fisiknya sama untuk mobil maupun gedung.
     */
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('ownership_type', 30)->nullable()->after('type');
        });

        Schema::table('branch_roll_prices', function (Blueprint $table) {
            $table->string('ownership_type', 30)->default('franchise')->after('id');
            $table->string('film_key', 100)->nullable()->after('ownership_type');
        });

        // Isi film_key dari nama kategori lama; nama yang sama di katalog mobil dan gedung digabung (harga terbaru dipakai).
        $rows = DB::table('branch_roll_prices')->orderBy('id')->get();
        foreach ($rows as $row) {
            $name = $row->category_product_building_id
                ? DB::table('category_product_building')->where('id', $row->category_product_building_id)->value('name')
                : DB::table('category_products')->where('id', $row->category_product_id)->value('name');
            $key = strtoupper(trim((string) $name));

            if ($key === '' || DB::table('branch_roll_prices')->where('film_key', $key)->exists()) {
                DB::table('branch_roll_prices')->where('id', $row->id)->delete();
                continue;
            }

            DB::table('branch_roll_prices')->where('id', $row->id)->update(['film_key' => $key]);
        }

        // Harga lama (satu harga per film) disalin ke kedua kategori agar perilaku tidak berubah sampai diatur.
        $now = now();
        foreach (DB::table('branch_roll_prices')->get() as $row) {
            foreach (array_slice(self::TYPES, 1) as $type) {
                DB::table('branch_roll_prices')->insert([
                    'ownership_type' => $type, 'film_key' => $row->film_key,
                    'price' => $row->price, 'unit' => $row->unit,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        Schema::table('branch_roll_prices', function (Blueprint $table) {
            $table->dropUnique(['category_product_id']);
            $table->dropUnique(['category_product_building_id']);
            $table->dropColumn(['category_product_id', 'category_product_building_id']);
        });

        Schema::table('branch_roll_prices', function (Blueprint $table) {
            $table->string('film_key', 100)->nullable(false)->change();
            $table->unique(['ownership_type', 'film_key'], 'brp_ownership_film_unique');
        });

        // Jejak kategori pembeli saat invoice dibuat (harga tidak berubah walau kategori cabang diganti).
        Schema::table('branch_roll_invoices', function (Blueprint $table) {
            $table->string('buyer_ownership_type', 30)->nullable()->after('to_branch_id');
        });
    }

    /** Rollback mengembalikan struktur lama tanpa data harga (id kategori tidak bisa direkonstruksi). */
    public function down(): void
    {
        Schema::table('branch_roll_invoices', function (Blueprint $table) {
            $table->dropColumn('buyer_ownership_type');
        });

        DB::table('branch_roll_prices')->delete();

        Schema::table('branch_roll_prices', function (Blueprint $table) {
            $table->dropUnique('brp_ownership_film_unique');
            $table->dropColumn(['ownership_type', 'film_key']);
            $table->unsignedBigInteger('category_product_id')->nullable()->unique();
            $table->unsignedBigInteger('category_product_building_id')->nullable()->unique();
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('ownership_type');
        });
    }
};
