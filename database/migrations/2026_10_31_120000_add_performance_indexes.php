<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** [tabel => [nama_index => kolom[]]] — kolom yang dipakai filter/urutan pada halaman daftar dan dashboard. */
    private array $indexes = [
        'commissions'     => ['idx_commissions_created_at' => ['created_at'], 'idx_commissions_emp_status_created' => ['employees_id', 'status', 'created_at']],
        'customers'       => ['idx_customers_branch_install' => ['branch_id', 'install_date']],
        'invoices'        => ['idx_invoices_status' => ['status'], 'idx_invoices_pelunasan_at' => ['pelunasan_at']],
        'invoice_building' => ['idx_invoice_building_status' => ['status'], 'idx_invoice_building_pelunasan_at' => ['pelunasan_at']],
        'ppf_invoices'    => ['idx_ppf_invoices_status' => ['status'], 'idx_ppf_invoices_date' => ['invoice_date']],
        'sales_orders'    => ['idx_sales_orders_status_date' => ['status', 'date']],
        'leads'           => ['idx_leads_status' => ['status']],
        'attendances'     => ['idx_attendances_date_branch' => ['attendance_date', 'branch_id']],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $defs) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($defs as $name => $columns) {
                if (! Schema::hasColumns($table, $columns) || $this->hasIndex($table, $name)) {
                    continue;
                }

                Schema::table($table, fn (Blueprint $t) => $t->index($columns, $name));
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $defs) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach (array_keys($defs) as $name) {
                if ($this->hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($name));
                }
            }
        }
    }

    private function hasIndex(string $table, string $name): bool
    {
        return collect(Schema::getIndexes($table))->contains(fn ($i) => $i['name'] === $name);
    }
};
