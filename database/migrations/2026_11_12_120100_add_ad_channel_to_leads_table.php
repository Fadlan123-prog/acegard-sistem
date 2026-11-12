<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('leads', 'ad_channel')) {
            return;
        }

        Schema::table('leads', function (Blueprint $table) {
            // Paid channel that generated this lead, distinct from the generic
            // `source` field — null means organic/not from a paid campaign.
            $table->enum('ad_channel', ['meta', 'tiktok', 'google', 'lainnya'])
                ->nullable()
                ->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('ad_channel');
        });
    }
};
