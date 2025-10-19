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
        Schema::create('user_merchant_payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('user_merchant_id')->constrained('user_merchants');
            $table->foreignId('user_merchant_wallet_id')->constrained('user_merchant_wallets', 'id', 'umpt_umw_id_foreign');
            $table->string('transaction_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['bank_transfer', 'cash', 'wallet', 'card']);
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('reference_number')->nullable(); // رقم الحوالة أو المرجع
            $table->date('payment_date');
            $table->timestamps();
            
            $table->index(['user_id', 'user_merchant_id'], 'umpt_user_merchant_idx');
            $table->index(['status'], 'umpt_status_idx');
            $table->index(['payment_date'], 'umpt_payment_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_merchant_payment_transactions');
    }
};
