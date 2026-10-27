<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dukungan atur-password aman untuk akun sales hasil pendaftaran:
 *  - users.must_change_password : admin menentukan password sementara → user wajib
 *    menggantinya saat login pertama.
 *  - users.password_changed_at  : NULL = user belum pernah memilih passwordnya sendiri
 *    (dipakai untuk membatasi "kirim ulang undangan").
 *  - sales_invitation_tokens    : token undangan (berlaku lebih lama dari reset biasa)
 *    disimpan di tabel terpisah agar masa berlaku kedua jenis tautan tidak bisa
 *    saling tertukar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('password');
            $table->timestamp('password_changed_at')->nullable()->after('must_change_password');
        });

        Schema::create('sales_invitation_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_invitation_tokens');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['must_change_password', 'password_changed_at']);
        });
    }
};
