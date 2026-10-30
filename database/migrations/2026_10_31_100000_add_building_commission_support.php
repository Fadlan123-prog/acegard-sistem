<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Meter yang dikerjakan tiap teknisi per kategori film (dicatat admin).
        // Komisi gedung = meter × tarif per meter kategori (commission.building_per_meter).
        Schema::create('customer_building_technician_meters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_building_id')->constrained('customer_building', 'id', 'fk_cbtm_customer')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees', 'id', 'fk_cbtm_employee')->cascadeOnDelete();
            $table->foreignId('category_product_building_id')->constrained('category_product_building', 'id', 'fk_cbtm_category')->cascadeOnDelete();
            $table->decimal('meters', 8, 2);
            $table->timestamps();

            $table->unique(['customer_building_id', 'employee_id', 'category_product_building_id'], 'uniq_building_tech_cat');
            $table->index('employee_id');
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->foreignId('invoice_building_id')->nullable()->after('invoice_id')
                ->constrained('invoice_building', 'id', 'fk_commissions_invoice_building')->nullOnDelete();
            $table->unique(['invoice_building_id', 'employees_id', 'role'], 'uniq_commission_invoice_building_emp_role');
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropUnique('uniq_commission_invoice_building_emp_role');
            $table->dropForeign('fk_commissions_invoice_building');
            $table->dropColumn('invoice_building_id');
        });

        Schema::dropIfExists('customer_building_technician_meters');
    }
};
