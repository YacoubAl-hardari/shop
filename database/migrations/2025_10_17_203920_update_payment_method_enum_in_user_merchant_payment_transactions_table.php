<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing 'wallet' values to 'wallet'
        // Update existing 'card' values to 'card'
        // These are already correct, no need to update
        
        // Modify the enum column to include all payment methods
        DB::statement("ALTER TABLE `user_merchant_payment_transactions` 
            MODIFY COLUMN `payment_method` 
            ENUM('bank_transfer', 'cash', 'check', 'card', 'wallet') 
            NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE `user_merchant_payment_transactions` 
            MODIFY COLUMN `payment_method` 
            ENUM('bank_transfer', 'cash', 'wallet', 'card') 
            NOT NULL");
    }
};
