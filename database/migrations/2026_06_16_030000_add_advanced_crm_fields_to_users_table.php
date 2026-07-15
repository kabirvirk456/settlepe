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
            $table->foreignId('assigned_sales_id')->nullable()->after('role')->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_rm_id')->nullable()->after('assigned_sales_id')->constrained('users')->nullOnDelete();
            $table->timestamp('follow_up_at')->nullable()->after('last_sales_contacted_at')->index();
            $table->string('priority', 30)->default('normal')->after('follow_up_at')->index();
            $table->string('call_disposition', 60)->nullable()->after('priority')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_sales_id');
            $table->dropConstrainedForeignId('assigned_rm_id');
            $table->dropColumn([
                'follow_up_at',
                'priority',
                'call_disposition',
            ]);
        });
    }
};
