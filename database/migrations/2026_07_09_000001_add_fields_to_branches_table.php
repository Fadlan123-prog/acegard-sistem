<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('code', 30)->nullable()->unique()->after('name');
            $table->enum('type', ['head_office', 'branch', 'warehouse', 'service_center'])->default('branch')->after('code');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('type');

            // Contact
            $table->string('phone', 30)->nullable()->after('address');
            $table->string('whatsapp', 30)->nullable()->after('phone');
            $table->string('email', 255)->nullable()->after('whatsapp');

            // Structured address
            $table->string('province', 150)->nullable()->after('city');
            $table->string('district', 150)->nullable()->after('province');
            $table->string('postal_code', 10)->nullable()->after('district');

            // Operational
            $table->time('opening_time')->nullable()->after('email');
            $table->time('closing_time')->nullable()->after('opening_time');
            $table->json('working_days')->nullable()->after('closing_time');

            // Management (nullable FKs to employees)
            $table->unsignedBigInteger('manager_id')->nullable()->after('working_days');
            $table->unsignedBigInteger('pic_id')->nullable()->after('manager_id');

            // Meta
            $table->date('opening_date')->nullable()->after('pic_id');
            $table->text('notes')->nullable()->after('opening_date');

            $table->foreign('manager_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('pic_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropForeign(['pic_id']);
            $table->dropColumn([
                'code', 'type', 'status',
                'phone', 'whatsapp', 'email',
                'province', 'district', 'postal_code',
                'opening_time', 'closing_time', 'working_days',
                'manager_id', 'pic_id',
                'opening_date', 'notes',
            ]);
        });
    }
};
