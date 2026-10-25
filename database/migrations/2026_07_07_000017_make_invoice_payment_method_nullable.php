<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Was NOT NULL; validation already allows null — align DB to match.
            // Payment method may be unknown at invoice creation (e.g. pending payment).
            $table->string('payment_method')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Backfill nulls before reverting to NOT NULL to avoid constraint violation.
            DB::table('invoices')
                ->whereNull('payment_method')
                ->update(['payment_method' => 'cash']);

            $table->string('payment_method')->nullable(false)->change();
        });
    }
};
