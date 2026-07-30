<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            // Drop the NOT-NULL FK so PPF rows (no automotive customer) can have customer_id = NULL.
            $table->dropForeign(['customer_id']);
            $table->unsignedBigInteger('customer_id')->nullable()->change();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();

            // PPF invoice reference (ppf_invoices is a separate table — plain integer, no cross-table FK).
            if (!Schema::hasColumn('commissions', 'ppf_invoice_id')) {
                $table->unsignedBigInteger('ppf_invoice_id')->nullable()->after('invoice_id');
                $table->index('ppf_invoice_id', 'commissions_ppf_invoice_id_index');
            }

            // Direct branch stamp — allows branch filtering without joining customers/ppf_customers.
            if (!Schema::hasColumn('commissions', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('ppf_invoice_id');
                $table->index('branch_id', 'commissions_branch_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();

            if (Schema::hasColumn('commissions', 'ppf_invoice_id')) {
                $table->dropIndex('commissions_ppf_invoice_id_index');
                $table->dropColumn('ppf_invoice_id');
            }

            if (Schema::hasColumn('commissions', 'branch_id')) {
                $table->dropIndex('commissions_branch_id_index');
                $table->dropColumn('branch_id');
            }
        });
    }
};
