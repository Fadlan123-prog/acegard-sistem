<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Performance index migration — idempotent.
 *
 * All 13 indexes are additive. Each is guarded with an existence check
 * so partial previous runs do not cause duplicate-key errors.
 * Every index has a corresponding drop in down() for safe rollback.
 */
return new class extends Migration
{
    public function up(): void
    {
        $existing = $this->existingIndexes();

        // ── customers ──────────────────────────────────────────────
        Schema::table('customers', function (Blueprint $table) use ($existing) {
            if (!in_array('idx_customers_branch_created', $existing)) {
                $table->index(['branch_id', 'created_at'], 'idx_customers_branch_created');
            }
            if (!in_array('idx_customers_tukang_type_date', $existing)) {
                $table->index(['tukang_id', 'install_type', 'created_at'], 'idx_customers_tukang_type_date');
            }
            if (!in_array('idx_customers_marketing', $existing)) {
                $table->index(['marketing_id'], 'idx_customers_marketing');
            }
        });

        // ── invoices ───────────────────────────────────────────────
        Schema::table('invoices', function (Blueprint $table) use ($existing) {
            if (!in_array('idx_invoices_customer_date', $existing)) {
                $table->index(['customer_id', 'invoice_date'], 'idx_invoices_customer_date');
            }
            if (!in_array('idx_invoices_date', $existing)) {
                $table->index(['invoice_date'], 'idx_invoices_date');
            }
            if (!in_array('idx_invoices_commission_released', $existing)) {
                $table->index(['commission_released_at'], 'idx_invoices_commission_released');
            }
        });

        // ── commissions ────────────────────────────────────────────
        Schema::table('commissions', function (Blueprint $table) use ($existing) {
            if (!in_array('idx_commissions_invoice_employee', $existing)) {
                $table->index(['invoice_id', 'employees_id'], 'idx_commissions_invoice_employee');
            }
            if (!in_array('idx_commissions_employee_status', $existing)) {
                $table->index(['employees_id', 'status'], 'idx_commissions_employee_status');
            }
            if (!in_array('idx_commissions_invoice_role', $existing)) {
                $table->index(['invoice_id', 'role'], 'idx_commissions_invoice_role');
            }
        });

        // ── attendances ────────────────────────────────────────────
        Schema::table('attendances', function (Blueprint $table) use ($existing) {
            if (!in_array('idx_attendances_branch_date', $existing)) {
                $table->index(['branch_id', 'attendance_date'], 'idx_attendances_branch_date');
            }
            if (!in_array('idx_attendances_employee_date', $existing)) {
                $table->index(['employee_id', 'attendance_date'], 'idx_attendances_employee_date');
            }
        });

        // ── petty_cash_transactions ────────────────────────────────
        Schema::table('petty_cash_transactions', function (Blueprint $table) use ($existing) {
            if (!in_array('idx_petty_cash_branch_date', $existing)) {
                $table->index(['branch_id', 'date'], 'idx_petty_cash_branch_date');
            }
        });

        // ── film_stock_pools ───────────────────────────────────────
        Schema::table('film_stock_pools', function (Blueprint $table) use ($existing) {
            if (!in_array('idx_film_stock_branch_type', $existing)) {
                $table->index(['branch_id', 'film_type'], 'idx_film_stock_branch_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_customers_branch_created');
            $table->dropIndexIfExists('idx_customers_tukang_type_date');
            $table->dropIndexIfExists('idx_customers_marketing');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_invoices_customer_date');
            $table->dropIndexIfExists('idx_invoices_date');
            $table->dropIndexIfExists('idx_invoices_commission_released');
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_commissions_invoice_employee');
            $table->dropIndexIfExists('idx_commissions_employee_status');
            $table->dropIndexIfExists('idx_commissions_invoice_role');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_attendances_branch_date');
            $table->dropIndexIfExists('idx_attendances_employee_date');
        });

        Schema::table('petty_cash_transactions', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_petty_cash_branch_date');
        });

        Schema::table('film_stock_pools', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_film_stock_branch_type');
        });
    }

    private function existingIndexes(): array
    {
        $rows = DB::select("
            SELECT DISTINCT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
            AND INDEX_NAME LIKE 'idx_%'
        ");

        return array_column($rows, 'INDEX_NAME');
    }
};
