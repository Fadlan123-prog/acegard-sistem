<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warranty_claims', function (Blueprint $table) {
            $table->id();

            $table->string('claim_number', 50)->unique(); // CLM-YYYYMM-0001
            $table->date('claim_date');

            // jenis warranty
            $table->enum('warranty_type', ['car_film', 'building_film', 'ppf']);

            // polymorphic sumber data
            $table->morphs('warrantable');
            // warrantable_type: App\Models\Customer | App\Models\CustomerBuilding | App\Models\CustomerPpfInstallation
            // warrantable_id  : id

            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('card_number', 255); // input dari customer

            $table->enum('cause', ['workmanship','product','user','unknown'])->default('unknown');
            $table->enum('status', ['submitted','inspected','approved','rejected','in_progress','completed','closed'])
                ->default('submitted');

            $table->boolean('is_chargeable')->default(false);
            $table->unsignedInteger('quote_total')->default(0);

            $table->text('complaint')->nullable();
            $table->text('inspection_notes')->nullable();
            $table->text('resolution_notes')->nullable();

            $table->timestamps();

            $table->index(['warranty_type','status']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranty_claims');
    }
};
