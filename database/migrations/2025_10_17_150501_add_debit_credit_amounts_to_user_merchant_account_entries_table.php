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
        Schema::table('user_merchant_account_entries', function (Blueprint $table) {
            $table->decimal('debit_amount', 10, 2)->default(0)->after('amount');
            $table->decimal('credit_amount', 10, 2)->default(0)->after('debit_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_merchant_account_entries', function (Blueprint $table) {
            $table->dropColumn(['debit_amount', 'credit_amount']);
        });
    }
};
