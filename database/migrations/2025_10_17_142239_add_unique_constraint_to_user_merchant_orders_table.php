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
        Schema::table('user_merchant_orders', function (Blueprint $table) {
            // Add unique constraint on user_id and order_number combination
            $table->unique(['user_id', 'order_number'], 'user_merchant_orders_user_order_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_merchant_orders', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique('user_merchant_orders_user_order_unique');
        });
    }
};
