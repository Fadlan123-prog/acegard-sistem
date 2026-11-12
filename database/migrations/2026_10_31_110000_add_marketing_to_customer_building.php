<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_building', function (Blueprint $table) {
            $table->foreignId('marketing_id')->nullable()->after('branch_id')
                ->constrained('employees', 'id', 'fk_customer_building_marketing')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer_building', function (Blueprint $table) {
            $table->dropForeign('fk_customer_building_marketing');
            $table->dropColumn('marketing_id');
        });
    }
};
