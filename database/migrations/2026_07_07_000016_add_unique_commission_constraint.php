<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove any duplicate rows before adding the constraint.
        // Keep the earliest (lowest id) row for each (invoice_id, employees_id, role) combo.
        DB::statement('
            DELETE c1 FROM commissions c1
            INNER JOIN commissions c2
            WHERE c1.invoice_id IS NOT NULL
              AND c1.invoice_id = c2.invoice_id
              AND c1.employees_id = c2.employees_id
              AND c1.role = c2.role
              AND c1.id > c2.id
        ');

        Schema::table('commissions', function (Blueprint $table) {
            // Unique per (invoice_id, employee, role) for car invoice commissions.
            // invoice_id is nullable (PPF commissions use ppf_invoice_id), so MySQL
            // allows multiple NULLs — the constraint only enforces non-null invoice_ids.
            $table->unique(['invoice_id', 'employees_id', 'role'], 'uniq_commission_invoice_emp_role');
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropUnique('uniq_commission_invoice_emp_role');
        });
    }
};
