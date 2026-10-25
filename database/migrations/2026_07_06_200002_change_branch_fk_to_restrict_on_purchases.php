<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Drop the existing CASCADE foreign key and re-add as RESTRICT
            // so that deleting a branch is blocked while it still has purchases.
            $table->dropForeign(['branch_id']);
            $table->foreign('branch_id')
                  ->references('id')
                  ->on('branches')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->foreign('branch_id')
                  ->references('id')
                  ->on('branches')
                  ->cascadeOnDelete();
        });
    }
};
