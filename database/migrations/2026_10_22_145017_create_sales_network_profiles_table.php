<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sengaja dibuat sebagai tabel terpisah, bukan kolom tambahan di `employees` —
     * field-field ini (kode referral, sponsor, status approval) cuma relevan untuk
     * karyawan yang berperan sales/marketing, bukan untuk seluruh karyawan. Pola
     * ini konsisten dengan tabel satelit employee lain yang sudah ada di sistem
     * (EmployeeHealthBalance, EmployeeDocument, EmployeeHistory).
     */
    public function up(): void
    {
        Schema::create('sales_network_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained('employees')->cascadeOnDelete();
            $table->string('referral_code', 20)->unique();

            // Sponsor/perekrut — dasar perhitungan rantai Stage 1/2/3 dan marketing
            // referral. Nullable: orang pertama di jaringan (akar) tidak punya sponsor.
            $table->foreignId('sponsor_id')->nullable()->constrained('employees')->nullOnDelete();

            $table->enum('status', ['pending_approval', 'active'])->default('pending_approval');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index('sponsor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_network_profiles');
    }
};
