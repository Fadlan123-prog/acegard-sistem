<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Supervisor QC gate, inserted between technician completion and pelunasan.
            // Nullable and left untouched on existing rows on purpose — this must not
            // retroactively block invoices completed under the pre-QC process. It is only
            // ever set to 'pending' going forward, by TeknisiDashboardController::completeJob().
            $table->string('qc_status')->nullable()->after('work_status'); // pending | passed | failed
            $table->text('qc_notes')->nullable()->after('qc_status');
            $table->foreignId('qc_reviewed_by')->nullable()->after('qc_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('qc_reviewed_at')->nullable()->after('qc_reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['qc_reviewed_by']);
            $table->dropColumn(['qc_status', 'qc_notes', 'qc_reviewed_by', 'qc_reviewed_at']);
        });
    }
};
