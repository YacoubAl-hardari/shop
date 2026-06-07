<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->decimal('customer_credit_applied', 12, 2)->default(0)->after('credit_amount');
        });

        Schema::table('merchant_customer_payments', function (Blueprint $table) {
            $table->decimal('applied_to_balance', 12, 2)->default(0)->after('amount');
            $table->decimal('surplus_to_credit', 12, 2)->default(0)->after('applied_to_balance');
        });
    }

    public function down(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->dropColumn('customer_credit_applied');
        });

        Schema::table('merchant_customer_payments', function (Blueprint $table) {
            $table->dropColumn(['applied_to_balance', 'surplus_to_credit']);
        });
    }
};
