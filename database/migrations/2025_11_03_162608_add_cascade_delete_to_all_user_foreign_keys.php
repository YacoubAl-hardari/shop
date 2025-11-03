<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get database name
        $database = DB::getDatabaseName();
        
        $tables = [
            'user_merchants',
            'user_merchant_orders',
            'user_merchant_account_statements',
            'user_merchant_payment_transactions',
            'user_merchant_account_entries',
        ];

        foreach ($tables as $table) {
            // Get the foreign key name for user_id
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = ? 
                AND COLUMN_NAME = 'user_id'
                AND REFERENCED_TABLE_NAME = 'users'
            ", [$database, $table]);

            foreach ($foreignKeys as $fk) {
                // Drop the existing foreign key
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            }

            // Add the foreign key with cascade delete
            DB::statement("
                ALTER TABLE `{$table}` 
                ADD CONSTRAINT `{$table}_user_id_foreign` 
                FOREIGN KEY (`user_id`) 
                REFERENCES `users` (`id`) 
                ON DELETE CASCADE
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get database name
        $database = DB::getDatabaseName();
        
        $tables = [
            'user_merchants',
            'user_merchant_orders',
            'user_merchant_account_statements',
            'user_merchant_payment_transactions',
            'user_merchant_account_entries',
        ];

        foreach ($tables as $table) {
            // Get the foreign key name for user_id
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = ? 
                AND COLUMN_NAME = 'user_id'
                AND REFERENCED_TABLE_NAME = 'users'
            ", [$database, $table]);

            foreach ($foreignKeys as $fk) {
                // Drop the cascade foreign key
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            }

            // Add the foreign key without cascade
            DB::statement("
                ALTER TABLE `{$table}` 
                ADD CONSTRAINT `{$table}_user_id_foreign` 
                FOREIGN KEY (`user_id`) 
                REFERENCES `users` (`id`)
            ");
        }
    }
};
