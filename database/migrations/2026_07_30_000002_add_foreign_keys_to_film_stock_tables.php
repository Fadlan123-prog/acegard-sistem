<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('film_stock_pools', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('category_product_id')->references('id')->on('category_products')->nullOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreign('category_product_building_id')->references('id')->on('category_product_building')->nullOnDelete();
            $table->foreign('product_building_id')->references('id')->on('product_building')->nullOnDelete();
        });

        Schema::table('film_stock_usages', function (Blueprint $table) {
            $table->foreign('film_stock_pool_id')->references('id')->on('film_stock_pools')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('customer_product_id')->references('id')->on('customer_product')->nullOnDelete();
            $table->foreign('customer_building_id')->references('id')->on('customer_building')->nullOnDelete();
            $table->foreign('customer_building_product_id')->references('id')->on('customer_building_product')->nullOnDelete();
            $table->foreign('category_product_id')->references('id')->on('category_products')->nullOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreign('part_id')->references('id')->on('parts')->nullOnDelete();
            $table->foreign('category_product_building_id')->references('id')->on('category_product_building')->nullOnDelete();
            $table->foreign('product_building_id')->references('id')->on('product_building')->nullOnDelete();
        });

        Schema::table('film_stock_transfers', function (Blueprint $table) {
            $table->foreign('from_branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('to_branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('received_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('film_complaints', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('customer_building_id')->references('id')->on('customer_building')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('film_complaint_items', function (Blueprint $table) {
            $table->foreign('film_complaint_id')->references('id')->on('film_complaints')->cascadeOnDelete();
            $table->foreign('category_product_id')->references('id')->on('category_products')->nullOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreign('part_id')->references('id')->on('parts')->nullOnDelete();
            $table->foreign('category_product_building_id')->references('id')->on('category_product_building')->nullOnDelete();
            $table->foreign('product_building_id')->references('id')->on('product_building')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('film_complaint_items', function (Blueprint $table) {
            $table->dropForeign(['film_complaint_id']);
            $table->dropForeign(['category_product_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['part_id']);
            $table->dropForeign(['category_product_building_id']);
            $table->dropForeign(['product_building_id']);
        });

        Schema::table('film_complaints', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['customer_building_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::table('film_stock_transfers', function (Blueprint $table) {
            $table->dropForeign(['from_branch_id']);
            $table->dropForeign(['to_branch_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['received_by']);
        });

        Schema::table('film_stock_usages', function (Blueprint $table) {
            $table->dropForeign(['film_stock_pool_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['customer_product_id']);
            $table->dropForeign(['customer_building_id']);
            $table->dropForeign(['customer_building_product_id']);
            $table->dropForeign(['category_product_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['part_id']);
            $table->dropForeign(['category_product_building_id']);
            $table->dropForeign(['product_building_id']);
        });

        Schema::table('film_stock_pools', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['category_product_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['category_product_building_id']);
            $table->dropForeign(['product_building_id']);
        });
    }
};
