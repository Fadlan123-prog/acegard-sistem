<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('spk_number')->nullable()->unique()->after('so_number');
            $table->string('spk_token', 64)->nullable()->unique()->after('spk_number');
            $table->timestamp('spk_issued_at')->nullable()->after('spk_token');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['spk_number', 'spk_token', 'spk_issued_at']);
        });
    }
};
