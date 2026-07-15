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
            $table->string('mobile', 10)->nullable()->unique();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('gender', 30)->nullable();
            $table->string('pan_card', 10)->nullable();
            $table->timestamp('cibil_profile_completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mobile',
                'age',
                'gender',
                'pan_card',
                'cibil_profile_completed_at',
            ]);
        });
    }
};
