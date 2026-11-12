<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Status baru 'cancelled' (tugas dibatalkan pemberi tugas) + alasan.
        DB::statement("ALTER TABLE creative_tasks MODIFY status ENUM('draft','in_progress','review','revision','done','cancelled') NOT NULL DEFAULT 'draft'");

        Schema::table('creative_tasks', function (Blueprint $table) {
            $table->text('cancel_reason')->nullable()->after('status');
            $table->timestamp('cancelled_at')->nullable()->after('cancel_reason');
        });
    }

    public function down(): void
    {
        DB::table('creative_tasks')->where('status', 'cancelled')->update(['status' => 'done']);

        Schema::table('creative_tasks', function (Blueprint $table) {
            $table->dropColumn(['cancel_reason', 'cancelled_at']);
        });

        DB::statement("ALTER TABLE creative_tasks MODIFY status ENUM('draft','in_progress','review','revision','done') NOT NULL DEFAULT 'draft'");
    }
};
