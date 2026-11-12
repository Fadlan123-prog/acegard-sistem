<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ad_spend_entries')) {
            return;
        }

        Schema::create('ad_spend_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->enum('channel', ['meta', 'tiktok', 'google', 'lainnya']);
            $table->date('spend_date');
            $table->decimal('amount', 14, 2);
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['branch_id', 'channel', 'spend_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_spend_entries');
    }
};
