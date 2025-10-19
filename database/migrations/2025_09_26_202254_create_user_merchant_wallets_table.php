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
        Schema::create('user_merchant_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_merchant_id')->constrained('user_merchants');
            $table->string('account_name', 100);
            $table->string('bank_account_number', 20);
            $table->string('bank_name', 50);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_merchant_wallets');
    }
};
