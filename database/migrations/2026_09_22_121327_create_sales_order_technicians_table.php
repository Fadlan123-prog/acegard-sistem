<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Sama pola dengan customer_ppf_installation_technicians: pivot untuk penugasan
// banyak teknisi (dipakai kategori Kaca Film Gedung, yang tidak punya kenek dan
// bisa ditangani lebih dari 2 orang sekaligus).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_order_technicians', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('sales_order_id');
            $table->unsignedBigInteger('employee_id');

            $table->timestamps();

            // FK name pendek biar tidak kena limit MySQL 64 char
            $table->foreign('sales_order_id', 'fk_so_tech_so')
                ->references('id')
                ->on('sales_orders')
                ->onDelete('cascade');

            $table->foreign('employee_id', 'fk_so_tech_emp')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');

            $table->unique(['sales_order_id', 'employee_id'], 'uniq_so_tech_pair');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_technicians');
    }
};
