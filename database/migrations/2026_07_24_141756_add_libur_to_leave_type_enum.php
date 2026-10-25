<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN leave_type ENUM('annual','sick','permission','emergency','libur') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN leave_type ENUM('annual','sick','permission','emergency') NOT NULL");
    }
};
