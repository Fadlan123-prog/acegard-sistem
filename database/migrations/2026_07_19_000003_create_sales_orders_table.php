<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            // Source references (optional)
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();

            // Document info
            $table->string('so_number')->unique();
            $table->date('date');
            $table->date('scheduled_date')->nullable();

            // Prospect / customer info
            $table->string('prospect_name');
            $table->string('prospect_phone')->nullable();
            $table->string('prospect_email')->nullable();
            $table->text('prospect_address')->nullable();

            // Category & vehicle
            $table->enum('category', ['car_film', 'building_film', 'ppf']);
            $table->string('vehicle_brand')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_year')->nullable();
            $table->string('vehicle_plate')->nullable();

            // Assigned staff
            $table->foreignId('tukang_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('kenek_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('marketing_id')->nullable()->constrained('employees')->nullOnDelete();

            // Pricing
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('total_amount')->default(0);

            // Status flow: pending → confirmed → in_progress → completed / cancelled
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])
                  ->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('in_progress_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();

            // Conversion pointer (to Customer / CustomerBuilding / CustomerPpf)
            $table->string('converted_to_type')->nullable();
            $table->unsignedBigInteger('converted_to_id')->nullable();
            $table->timestamp('converted_at')->nullable();

            // Misc
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('description');
            $table->string('specifications')->nullable();
            $table->string('unit')->default('unit');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
    }
};
