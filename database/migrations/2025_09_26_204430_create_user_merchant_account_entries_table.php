<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_merchant_account_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('user_merchant_id')->constrained('user_merchants');
            $table->string('entry_number');
            $table->enum('entry_type', ['debit', 'credit']);
            $table->decimal('amount', 10, 2);
            $table->decimal('debit_amount', 10, 2)->default(0);
            $table->decimal('credit_amount', 10, 2)->default(0);
            $table->string('description');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('balance_after', 10, 2);
            $table->date('entry_date');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['user_id', 'user_merchant_id'], 'umae_user_merchant_idx');
            $table->index(['entry_date'], 'umae_entry_date_idx');
            $table->index(['entry_type'], 'umae_entry_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_merchant_account_entries');
    }
};