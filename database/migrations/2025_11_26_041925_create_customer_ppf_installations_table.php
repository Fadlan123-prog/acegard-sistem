<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_ppf_installations', function (Blueprint $table) {
            $table->id();

            // Relasi utama
            $table->foreignId('customer_ppf_id')
                ->constrained('customer_ppf')
                ->cascadeOnDelete();

            $table->foreignId('ppf_product_id')
                ->constrained('ppf_products')
                ->restrictOnDelete();

            $table->foreignId('branch_id')
                ->constrained()
                ->restrictOnDelete();

            // (OPSIONAL tapi biasanya kamu pakai di form)
            $table->string('install_type')->nullable();

            $table->date('install_date');

            // legacy (opsional) - kalau masih dipakai
            $table->foreignId('tukang_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

            $table->foreignId('kenek_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

            // Data garansi
            $table->string('warranty_code')->unique();
            $table->date('warranty_start');
            $table->unsignedTinyInteger('warranty_years')->default(5);
            $table->date('warranty_end')->nullable();

            $table->enum('warranty_status', ['active', 'expired', 'void'])
                ->default('active');

            // partial panels
            $table->json('panels')->nullable();

            // override terms
            $table->text('coverage_override')->nullable();
            $table->text('exclusions_override')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });

        Schema::create('customer_ppf_installation_technicians', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('customer_ppf_installation_id');
            $table->unsignedBigInteger('employee_id');

            $table->timestamps();

            // FK name pendek biar tidak kena limit MySQL 64 char
            $table->foreign('customer_ppf_installation_id', 'fk_ppf_install_tech_install')
                ->references('id')
                ->on('customer_ppf_installations')
                ->onDelete('cascade');

            $table->foreign('employee_id', 'fk_ppf_install_tech_emp')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');

            // unique name pendek juga
            $table->unique(
                ['customer_ppf_installation_id', 'employee_id'],
                'ux_ppf_install_tech'
            );
        });
    }

    public function down(): void
    {
        // drop pivot dulu baru parent table
        Schema::dropIfExists('customer_ppf_installation_technicians');
        Schema::dropIfExists('customer_ppf_installations');
    }
};
