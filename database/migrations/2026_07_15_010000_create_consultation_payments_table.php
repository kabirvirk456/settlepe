<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('razorpay');
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('INR');
            $table->string('receipt', 40)->unique();
            $table->string('order_id')->nullable()->unique();
            $table->string('payment_id')->nullable()->unique();
            $table->string('status', 30)->default('creating')->index();
            $table->text('failure_reason')->nullable();
            $table->longText('provider_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_payments');
    }
};
