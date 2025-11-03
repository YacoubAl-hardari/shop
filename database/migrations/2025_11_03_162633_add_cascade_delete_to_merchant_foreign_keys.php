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
        
        // Tables with user_merchant_id foreign key
        $merchantTables = [
            'user_merchant_wallets',
            'user_merchant_products',
            'user_merchant_orders',
            'user_merchant_account_statements',
            'user_merchant_payment_transactions',
            'user_merchant_account_entries',
        ];

        foreach ($merchantTables as $table) {
            // Get the foreign key name for user_merchant_id
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = ? 
                AND COLUMN_NAME = 'user_merchant_id'
                AND REFERENCED_TABLE_NAME = 'user_merchants'
            ", [$database, $table]);

            foreach ($foreignKeys as $fk) {
                // Drop the existing foreign key
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            }

            // Add the foreign key with cascade delete
            DB::statement("
                ALTER TABLE `{$table}` 
                ADD CONSTRAINT `{$table}_user_merchant_id_foreign` 
                FOREIGN KEY (`user_merchant_id`) 
                REFERENCES `user_merchants` (`id`) 
                ON DELETE CASCADE
            ");
        }

        // Handle user_merchant_payment_transactions wallet foreign key separately
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = 'user_merchant_payment_transactions' 
            AND COLUMN_NAME = 'user_merchant_wallet_id'
            AND REFERENCED_TABLE_NAME = 'user_merchant_wallets'
        ", [$database]);

        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE `user_merchant_payment_transactions` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        DB::statement("
            ALTER TABLE `user_merchant_payment_transactions` 
            ADD CONSTRAINT `umpt_umw_id_foreign` 
            FOREIGN KEY (`user_merchant_wallet_id`) 
            REFERENCES `user_merchant_wallets` (`id`) 
            ON DELETE CASCADE
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get database name
        $database = DB::getDatabaseName();
        
        $merchantTables = [
            'user_merchant_wallets',
            'user_merchant_products',
            'user_merchant_orders',
            'user_merchant_account_statements',
            'user_merchant_payment_transactions',
            'user_merchant_account_entries',
        ];

        foreach ($merchantTables as $table) {
            // Get the foreign key name for user_merchant_id
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = ? 
                AND COLUMN_NAME = 'user_merchant_id'
                AND REFERENCED_TABLE_NAME = 'user_merchants'
            ", [$database, $table]);

            foreach ($foreignKeys as $fk) {
                // Drop the cascade foreign key
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            }

            // Add the foreign key without cascade
            DB::statement("
                ALTER TABLE `{$table}` 
                ADD CONSTRAINT `{$table}_user_merchant_id_foreign` 
                FOREIGN KEY (`user_merchant_id`) 
                REFERENCES `user_merchants` (`id`)
            ");
        }

        // Revert wallet foreign key
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = 'user_merchant_payment_transactions' 
            AND COLUMN_NAME = 'user_merchant_wallet_id'
            AND REFERENCED_TABLE_NAME = 'user_merchant_wallets'
        ", [$database]);

        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE `user_merchant_payment_transactions` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        DB::statement("
            ALTER TABLE `user_merchant_payment_transactions` 
            ADD CONSTRAINT `umpt_umw_id_foreign` 
            FOREIGN KEY (`user_merchant_wallet_id`) 
            REFERENCES `user_merchant_wallets` (`id`)
        ");
    }
};
