<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('accepted_at')->nullable()->after('completed_at');
            $table->string('job_photo')->nullable()->after('accepted_at');
            $table->string('checklist_photo')->nullable()->after('job_photo');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['accepted_at', 'job_photo', 'checklist_photo']);
        });
    }
};
