<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customer_ppf_installations', function (Blueprint $table) {
            $table->string('checklist_image')->nullable()->after('panels');
            $table->string('job_order_image')->nullable()->after('checklist_image');
        });

        // OPTIONAL: kalau sebelumnya kamu sudah buat kolom override image, dan mau dibuang:
        // (hapus block ini kalau tidak perlu)
        if (Schema::hasColumn('customer_ppf_installations', 'coverage_override_image')) {
            Schema::table('customer_ppf_installations', function (Blueprint $table) {
                $table->dropColumn(['coverage_override_image', 'exclusions_override_image']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('customer_ppf_installations', function (Blueprint $table) {
            $table->dropColumn(['checklist_image', 'job_order_image']);
        });


        Schema::table('customer_ppf_installations', function (Blueprint $table) {
            $table->string('coverage_override')->nullable();
            $table->string('exclusions_override')->nullable();
        });
    }
};
