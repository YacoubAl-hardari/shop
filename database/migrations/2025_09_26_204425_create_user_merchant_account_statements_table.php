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
        Schema::create('user_merchant_account_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('user_merchant_id')->constrained('user_merchants');
            $table->decimal('debit_amount', 10, 2)->default(0); // مدين - المبلغ المستحق على المستخدم
            $table->decimal('credit_amount', 10, 2)->default(0); // دائن - المبلغ المدفوع من المستخدم
            $table->decimal('balance', 10, 2)->default(0); // الرصيد المتبقي
            $table->string('transaction_type'); // order, payment, refund
            $table->string('reference_type')->nullable(); // order, payment_transaction
            $table->unsignedBigInteger('reference_id')->nullable(); // معرف المرجع
            $table->text('description')->nullable();
            $table->date('transaction_date');
            $table->timestamps();
            
            $table->index(['user_id', 'user_merchant_id']);
            $table->index(['transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_merchant_account_statements');
    }
};
