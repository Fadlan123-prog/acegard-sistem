<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['card_number']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('card_number')->change();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->unique('card_number');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['card_number']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('card_number', 12, 0)->change();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->unique('card_number');
        });
    }
};
