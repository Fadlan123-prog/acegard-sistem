<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the purchases status enum to cover the full GRN flow.
        // 'confirmed' kept for backward-compat with existing rows (= sent).
        DB::statement("ALTER TABLE purchases MODIFY COLUMN status ENUM(
            'draft',
            'confirmed',
            'sent',
            'partial_received',
            'received',
            'matched',
            'cancelled'
        ) NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        // Revert only if no rows use the new statuses
        DB::statement("ALTER TABLE purchases MODIFY COLUMN status ENUM(
            'draft',
            'confirmed',
            'cancelled'
        ) NOT NULL DEFAULT 'confirmed'");
    }
};
