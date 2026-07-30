<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_adjustments', function (Blueprint $table) {
            $table->bigInteger('uang_motor')->default(0)->unsigned(false)->change();
            $table->bigInteger('uang_makan')->default(0)->unsigned(false)->change();
            $table->bigInteger('potongan_rokok')->default(0)->unsigned(false)->change();
            $table->bigInteger('kasbon')->default(0)->unsigned(false)->change();
            $table->bigInteger('potongan_absen')->default(0)->unsigned(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('salary_adjustments', function (Blueprint $table) {
            $table->unsignedBigInteger('uang_motor')->default(0)->change();
            $table->unsignedBigInteger('uang_makan')->default(0)->change();
            $table->unsignedBigInteger('potongan_rokok')->default(0)->change();
            $table->unsignedBigInteger('kasbon')->default(0)->change();
            $table->unsignedBigInteger('potongan_absen')->default(0)->change();
        });
    }
};
