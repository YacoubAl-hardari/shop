<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Find and drop foreign key constraints that use the unique index
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'user_merchant_orders' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($foreignKeys as $fk) {
            try {
                DB::statement("ALTER TABLE user_merchant_orders DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            } catch (\Exception $e) {
                // Continue if FK doesn't exist
            }
        }
        
        // Now drop the unique constraint
        try {
            DB::statement("ALTER TABLE user_merchant_orders DROP INDEX user_merchant_orders_user_order_unique");
        } catch (\Exception $e) {
            // Constraint might already be dropped
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE user_merchant_orders ADD UNIQUE (user_id, order_number)");
    }
};