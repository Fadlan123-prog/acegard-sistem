<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Kategori cabang (franchise / non_franchise) untuk menentukan harga roll antar cabang. */
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
