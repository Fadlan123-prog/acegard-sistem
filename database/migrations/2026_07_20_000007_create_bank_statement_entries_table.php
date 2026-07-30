<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_statement_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->date('entry_date');
            $table->string('description');
            $table->bigInteger('debit')->default(0);
            $table->bigInteger('credit')->default(0);
            $table->bigInteger('balance_after')->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->boolean('is_reconciled')->default(false);
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reconciled_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->index(['bank_account_id', 'entry_date']);
            $table->index(['bank_account_id', 'is_reconciled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_statement_entries');
    }
};
