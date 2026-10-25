<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('film_roll_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('film_roll_entries', 'current_branch_id')) {
                $table->unsignedBigInteger('current_branch_id')->nullable()->after('branch_id');
            }

            if (!Schema::hasColumn('film_roll_entries', 'status')) {
                $table->enum('status', [
                    'in_warehouse',
                    'in_branch',
                    'used',
                    'damaged',
                    'lost'
                ])->default('in_warehouse')->after('current_branch_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('film_roll_entries', function (Blueprint $table) {
            if (Schema::hasColumn('film_roll_entries', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('film_roll_entries', 'current_branch_id')) {
                $table->dropColumn('current_branch_id');
            }
        });
    }
};
