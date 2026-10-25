<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_flow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_flow_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('step_order');
            $table->string('approver_role'); // Spatie role name, e.g. 'manager_operasional'
            $table->boolean('same_branch_as_subject')->default(false);
            $table->timestamps();

            $table->unique(['approval_flow_id', 'step_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_flow_steps');
    }
};
