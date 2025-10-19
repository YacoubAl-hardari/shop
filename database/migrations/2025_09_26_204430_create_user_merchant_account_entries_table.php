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
        Schema::create('user_merchant_account_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('user_merchant_id')->constrained('user_merchants');
            $table->string('entry_number')->unique();
            $table->enum('entry_type', ['debit', 'credit']); // مدين أو دائن
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->string('reference_type')->nullable(); // order, payment_transaction, manual
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('balance_after', 10, 2); // الرصيد بعد القيد
            $table->date('entry_date');
            $table->foreignId('created_by')->constrained('users'); // من قام بإنشاء القيد
            $table->timestamps();
            
            $table->index(['user_id', 'user_merchant_id'], 'umae_user_merchant_idx');
            $table->index(['entry_date'], 'umae_entry_date_idx');
            $table->index(['entry_type'], 'umae_entry_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_merchant_account_entries');
    }
};
