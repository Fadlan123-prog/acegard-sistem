<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** 'in_transit': roll sudah dijual ke cabang lain lewat invoice tapi belum diterima cabang pembeli. */
    public function up(): void
    {
        DB::statement("ALTER TABLE film_roll_entries MODIFY COLUMN status ENUM(
            'in_warehouse', 'in_branch', 'used', 'damaged', 'lost', 'sold_to_mitra', 'in_transit'
        ) DEFAULT 'in_warehouse'");
    }

    public function down(): void
    {
        DB::statement("UPDATE film_roll_entries SET status = 'in_warehouse' WHERE status = 'in_transit'");
        DB::statement("ALTER TABLE film_roll_entries MODIFY COLUMN status ENUM(
            'in_warehouse', 'in_branch', 'used', 'damaged', 'lost', 'sold_to_mitra'
        ) DEFAULT 'in_warehouse'");
    }
};
