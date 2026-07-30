<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppf_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('ppf_invoices', 'commission_released_at')) {
                $table->timestamp('commission_released_at')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('ppf_invoices', 'commission_released_by')) {
                $table->unsignedBigInteger('commission_released_by')->nullable()->after('commission_released_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ppf_invoices', function (Blueprint $table) {
            $table->dropColumnIfExists('commission_released_by');
            $table->dropColumnIfExists('commission_released_at');
        });
    }
};
