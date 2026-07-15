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
        Schema::create('settlement_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('bank_name');
            $table->unsignedBigInteger('outstanding_amount');
            $table->unsignedBigInteger('offered_settlement_amount')->nullable();
            $table->unsignedBigInteger('final_settlement_amount')->nullable();
            $table->date('due_date')->nullable();
            $table->string('closure_letter_status', 60)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlement_accounts');
    }
};
