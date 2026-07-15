<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('case_stage', 80)->nullable()->after('rm_status');
            $table->string('case_reference', 40)->nullable()->unique()->after('case_stage');
        });

        Schema::table('lead_activities', function (Blueprint $table) {
            $table->boolean('customer_visible')->default(false)->after('notes');
        });

        Schema::table('lead_documents', function (Blueprint $table) {
            $table->string('review_status', 30)->default('pending')->after('document_type');
            $table->text('review_notes')->nullable()->after('review_status');
            $table->boolean('customer_visible')->default(true)->after('review_notes');
        });

        Schema::table('settlement_accounts', function (Blueprint $table) {
            $table->string('product')->nullable()->after('bank_name');
            $table->string('account_reference', 80)->nullable()->after('product');
            $table->string('stage', 60)->default('assessment')->after('account_reference');
            $table->boolean('customer_visible')->default(true)->after('stage');
        });

        Schema::create('customer_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority', 20)->default('normal');
            $table->string('status', 30)->default('pending');
            $table->dateTime('due_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('legal_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('lender_name');
            $table->string('notice_type');
            $table->date('received_at');
            $table->date('response_due_at')->nullable();
            $table->string('priority', 20)->default('normal');
            $table->string('status', 40)->default('received');
            $table->text('customer_instructions')->nullable();
            $table->string('original_name')->nullable();
            $table->string('path')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_notices');
        Schema::dropIfExists('customer_tasks');

        Schema::table('settlement_accounts', function (Blueprint $table) {
            $table->dropColumn(['product', 'account_reference', 'stage', 'customer_visible']);
        });
        Schema::table('lead_documents', function (Blueprint $table) {
            $table->dropColumn(['review_status', 'review_notes', 'customer_visible']);
        });
        Schema::table('lead_activities', function (Blueprint $table) {
            $table->dropColumn('customer_visible');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['case_reference']);
            $table->dropColumn(['case_stage', 'case_reference']);
        });
    }
};
