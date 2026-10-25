<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            if (!Schema::hasColumn('commissions', 'role')) {
                $table->string('role')->nullable()->after('amount');
            }

            if (!Schema::hasColumn('commissions', 'install_type')) {
                $table->string('install_type')->nullable()->after('role');
            }

            if (!Schema::hasColumn('commissions', 'category_product_id')) {
                $table->unsignedBigInteger('category_product_id')->nullable()->after('install_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            if (Schema::hasColumn('commissions', 'category_product_id')) {
                $table->dropColumn('category_product_id');
            }

            if (Schema::hasColumn('commissions', 'install_type')) {
                $table->dropColumn('install_type');
            }

            if (Schema::hasColumn('commissions', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
