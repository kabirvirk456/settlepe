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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('customer')->index();
            $table->string('sales_status', 60)->nullable()->index();
            $table->text('sales_notes')->nullable();
            $table->timestamp('last_sales_contacted_at')->nullable();
            $table->timestamp('consultation_fee_paid_at')->nullable();
            $table->timestamp('service_fee_paid_at')->nullable();
            $table->string('rm_status', 60)->nullable()->index();
            $table->text('rm_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'sales_status',
                'sales_notes',
                'last_sales_contacted_at',
                'consultation_fee_paid_at',
                'service_fee_paid_at',
                'rm_status',
                'rm_notes',
            ]);
        });
    }
};
