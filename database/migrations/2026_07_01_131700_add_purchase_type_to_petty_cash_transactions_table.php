<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL requires re-declaring the enum to add a new value
        \DB::statement("ALTER TABLE petty_cash_transactions MODIFY COLUMN type ENUM('in','out','purchase') NOT NULL");

        Schema::table('petty_cash_transactions', function (Blueprint $table) {
            $table->string('item_name')->nullable()->after('destination');
        });
    }

    public function down(): void
    {
        Schema::table('petty_cash_transactions', function (Blueprint $table) {
            $table->dropColumn('item_name');
        });

        \DB::statement("ALTER TABLE petty_cash_transactions MODIFY COLUMN type ENUM('in','out') NOT NULL");
    }
};
