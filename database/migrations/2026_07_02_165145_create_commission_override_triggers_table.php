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
        Schema::create('commission_override_triggers', function (Blueprint $table) {
            $table->id();
            // Karyawan yang menerima override komisi (misal: Adit)
            $table->foreignId('override_employee_id')->constrained('employees')->cascadeOnDelete();
            // Marketing yang memicu komisi override ini
            $table->foreignId('trigger_marketing_id')->constrained('employees')->cascadeOnDelete();
            // Rate persentase (misal: 0.02 = 2%)
            $table->decimal('rate', 5, 4)->default(0.0200);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['override_employee_id', 'trigger_marketing_id'], 'unique_override_trigger');
        });

        // Seed: Adit mendapat komisi dari semua marketing yang BUKAN Dinda
        $adit = \DB::table('employees')->whereRaw('LOWER(name) = ?', ['adit'])->first();
        $dinda = \DB::table('employees')->whereRaw('LOWER(name) = ?', ['dinda'])->first();

        if ($adit) {
            $marketings = \DB::table('employees')
                ->where('job_position', 'Marketing')
                ->where('id', '!=', $dinda?->id ?? 0)
                ->get();

            foreach ($marketings as $m) {
                \DB::table('commission_override_triggers')->insert([
                    'override_employee_id' => $adit->id,
                    'trigger_marketing_id' => $m->id,
                    'rate'                 => 0.0200,
                    'is_active'            => true,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_override_triggers');
    }
};
