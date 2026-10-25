<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('employees', 'phone_number')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('phone_number')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('employees', 'phone_number')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('phone_number');
            });
        }
    }
};
