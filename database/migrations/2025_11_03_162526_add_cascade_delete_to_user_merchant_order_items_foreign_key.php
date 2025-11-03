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
        
        // Get the foreign key for user_merchant_order_id
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = 'user_merchant_order_items' 
            AND COLUMN_NAME = 'user_merchant_order_id'
            AND REFERENCED_TABLE_NAME = 'user_merchant_orders'
        ", [$database]);

        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE `user_merchant_order_items` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        // Get the foreign key for user_merchant_product_id
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = 'user_merchant_order_items' 
            AND COLUMN_NAME = 'user_merchant_product_id'
            AND REFERENCED_TABLE_NAME = 'user_merchant_products'
        ", [$database]);

        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE `user_merchant_order_items` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        // Add the foreign keys with cascade delete
        DB::statement("
            ALTER TABLE `user_merchant_order_items` 
            ADD CONSTRAINT `user_merchant_order_items_user_merchant_order_id_foreign` 
            FOREIGN KEY (`user_merchant_order_id`) 
            REFERENCES `user_merchant_orders` (`id`) 
            ON DELETE CASCADE
        ");

        DB::statement("
            ALTER TABLE `user_merchant_order_items` 
            ADD CONSTRAINT `user_merchant_order_items_user_merchant_product_id_foreign` 
            FOREIGN KEY (`user_merchant_product_id`) 
            REFERENCES `user_merchant_products` (`id`) 
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
        
        // Get the foreign key for user_merchant_order_id
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = 'user_merchant_order_items' 
            AND COLUMN_NAME = 'user_merchant_order_id'
            AND REFERENCED_TABLE_NAME = 'user_merchant_orders'
        ", [$database]);

        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE `user_merchant_order_items` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        // Get the foreign key for user_merchant_product_id
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = 'user_merchant_order_items' 
            AND COLUMN_NAME = 'user_merchant_product_id'
            AND REFERENCED_TABLE_NAME = 'user_merchant_products'
        ", [$database]);

        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE `user_merchant_order_items` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        // Add the foreign keys without cascade
        DB::statement("
            ALTER TABLE `user_merchant_order_items` 
            ADD CONSTRAINT `user_merchant_order_items_user_merchant_order_id_foreign` 
            FOREIGN KEY (`user_merchant_order_id`) 
            REFERENCES `user_merchant_orders` (`id`)
        ");

        DB::statement("
            ALTER TABLE `user_merchant_order_items` 
            ADD CONSTRAINT `user_merchant_order_items_user_merchant_product_id_foreign` 
            FOREIGN KEY (`user_merchant_product_id`) 
            REFERENCES `user_merchant_products` (`id`)
        ");
    }
};
