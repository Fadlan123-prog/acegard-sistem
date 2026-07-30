<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('has_panoramic')->default(false)->after('install_type');
        });
        // No backfill — a SUNROOF/PANORAMIC part row does NOT mean the customer
        // qualifies for panoramic commission (regular sunroof also uses that part).
        // Users must explicitly enable the toggle per customer.
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('has_panoramic');
        });
    }
};
