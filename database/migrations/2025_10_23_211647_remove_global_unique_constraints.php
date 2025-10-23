<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Safely remove constraints using raw SQL
        try {
            DB::statement("ALTER TABLE user_merchant_orders DROP INDEX user_merchant_orders_user_order_unique");
        } catch (\Exception $e) {
            // Index might already be dropped or renamed
        }
        
        try {
            DB::statement("ALTER TABLE user_merchant_account_entries DROP INDEX user_merchant_account_entries_entry_number_unique");
        } catch (\Exception $e) {
            // Index might already be dropped
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE user_merchant_account_entries ADD UNIQUE (entry_number)");
        DB::statement("ALTER TABLE user_merchant_orders ADD UNIQUE (user_id, order_number)");
    }
};