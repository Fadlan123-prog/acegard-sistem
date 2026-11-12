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
            // Sejajar invoice_id/ppf_invoice_id yang sudah ada — nullable, hanya
            // diisi untuk komisi dari fitur referral sales (sales_closing,
            // referral_stage_1/2/3, marketing_referral di kolom `role`).
            $table->foreignId('sales_transaction_id')->nullable()
                ->after('ppf_invoice_id')
                ->constrained('sales_transactions')->cascadeOnDelete();

            // Pola sama persis dengan uniq_commission_invoice_emp_role (migration
            // 2026_07_07_000016) — MySQL mengizinkan banyak NULL di kolom ini,
            // jadi tidak bentrok dengan baris invoice_id/ppf_invoice_id yang sudah ada.
            $table->unique(['sales_transaction_id', 'employees_id', 'role'], 'uniq_commission_sales_tx_emp_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropUnique('uniq_commission_sales_tx_emp_role');
            $table->dropConstrainedForeignId('sales_transaction_id');
        });
    }
};
