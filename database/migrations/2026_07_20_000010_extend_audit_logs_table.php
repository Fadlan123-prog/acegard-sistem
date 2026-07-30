<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'user_name')) {
                $table->string('user_name', 100)->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('audit_logs', 'role')) {
                $table->string('role', 50)->nullable()->after('user_name');
            }
            if (!Schema::hasColumn('audit_logs', 'branch_name')) {
                $table->string('branch_name', 100)->nullable()->after('branch_id');
            }
            if (!Schema::hasColumn('audit_logs', 'module')) {
                $table->string('module', 80)->nullable()->after('event');
            }
            if (!Schema::hasColumn('audit_logs', 'description')) {
                $table->string('description', 255)->nullable()->after('module');
            }
            if (!Schema::hasColumn('audit_logs', 'url')) {
                $table->string('url', 500)->nullable()->after('description');
            }
            if (!Schema::hasColumn('audit_logs', 'method')) {
                $table->string('method', 10)->nullable()->after('url');
            }
            if (!Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn([
                'user_name', 'role', 'branch_name', 'module',
                'description', 'url', 'method', 'user_agent',
            ]);
        });
    }
};
