<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_customer_financial_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('merchant_customer_id');
            $table->unsignedBigInteger('statement_share_id')->nullable();
            $table->foreignId('submitted_by')->constrained('users');
            $table->unsignedBigInteger('merchant_payment_account_id')->nullable();
            $table->string('payment_method')->default('cash');
            $table->string('purpose')->default('settlement');
            $table->decimal('amount', 12, 2);
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('merchant_customer_payment_id')->nullable();
            $table->timestamps();

            $table->foreign('merchant_customer_id', 'mcft_customer_fk')
                ->references('id')->on('merchant_customers')->cascadeOnDelete();
            $table->foreign('statement_share_id', 'mcft_share_fk')
                ->references('id')->on('merchant_customer_statement_shares')->nullOnDelete();
            $table->foreign('merchant_payment_account_id', 'mcft_pay_acct_fk')
                ->references('id')->on('merchant_payment_accounts')->nullOnDelete();
            $table->foreign('merchant_customer_payment_id', 'mcft_payment_fk')
                ->references('id')->on('merchant_customer_payments')->nullOnDelete();

            $table->index(['team_id', 'status'], 'mcft_team_status_idx');
            $table->index(['merchant_customer_id', 'status'], 'mcft_customer_status_idx');
            $table->index(['statement_share_id', 'status'], 'mcft_share_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_customer_financial_transfers');
    }
};
