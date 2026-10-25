<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE film_roll_entries MODIFY COLUMN status ENUM(
            'in_warehouse', 'in_branch', 'used', 'damaged', 'lost', 'sold_to_mitra'
        ) DEFAULT 'in_warehouse'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE film_roll_entries MODIFY COLUMN status ENUM(
            'in_warehouse', 'in_branch', 'used', 'damaged', 'lost'
        ) DEFAULT 'in_warehouse'");
    }
};
