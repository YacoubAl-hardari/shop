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
        // Add team_id to all tenant-scoped tables
        $tables = [
            'user_merchants',
            'user_merchant_products',
            'user_merchant_orders',
            'user_merchant_order_items',
            'user_merchant_wallets',
            'user_merchant_account_statements',
            'user_merchant_payment_transactions',
            'user_merchant_account_entries',
            'budgets',
            'budget_categories',
            'budget_alerts',
            'merchant_categories',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->foreignId('team_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'user_merchants',
            'user_merchant_products',
            'user_merchant_orders',
            'user_merchant_order_items',
            'user_merchant_wallets',
            'user_merchant_account_statements',
            'user_merchant_payment_transactions',
            'user_merchant_account_entries',
            'budgets',
            'budget_categories',
            'budget_alerts',
            'merchant_categories',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['team_id']);
                    $table->dropColumn('team_id');
                });
            }
        }
    }
};
