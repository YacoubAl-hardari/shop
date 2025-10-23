<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove any remaining order number constraints
        $constraints = [
            'user_merchant_orders_user_order_unique',
            'umao_user_merchant_order_number_unique',
            'user_merchant_account_entries_entry_number_unique'
        ];
        
        foreach ($constraints as $constraint) {
            try {
                DB::statement("ALTER TABLE user_merchant_orders DROP INDEX {$constraint}");
            } catch (\Exception $e) {
                // Constraint might not exist
            }
        }
        
        foreach ($constraints as $constraint) {
            try {
                DB::statement("ALTER TABLE user_merchant_account_entries DROP INDEX {$constraint}");
            } catch (\Exception $e) {
                // Constraint might not exist
            }
        }
    }

    public function down(): void
    {
        // No rollback needed - we want these constraints removed
    }
};