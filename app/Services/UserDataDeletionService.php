<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserDataDeletionService
{
    /**
     * Delete user account and all related data following proper constraints
     */
    public function deleteUserAccount(User $user): bool
    {
        DB::beginTransaction();

        try {
            Log::info("Starting deletion process for user: {$user->id}");

            // Delete all merchants and their related data
            foreach ($user->merchants as $merchant) {
                $this->deleteMerchantData($merchant);
            }

            // Delete user's direct relations
            $this->deleteUserDirectRelations($user);

            // Finally, delete the user
            $user->delete();

            DB::commit();
            Log::info("Successfully deleted user: {$user->id}");
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to delete user: {$user->id}. Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete all merchant related data in correct order
     */
    protected function deleteMerchantData($merchant): void
    {
        Log::info("Deleting data for merchant: {$merchant->id}");

        // 1. Delete order items first (child of orders)
        foreach ($merchant->orders as $order) {
            $order->orderItems()->delete();
        }

        // 2. Delete orders (now they have no items)
        $merchant->orders()->delete();

        // 3. Delete account entries (no foreign key dependencies)
        $merchant->accountEntries()->delete();

        // 4. Delete account statements (no foreign key dependencies)
        $merchant->accountStatements()->delete();

        // 5. Delete payment transactions (before wallets)
        $merchant->paymentTransactions()->delete();

        // 6. Delete products (now no order items reference them)
        $merchant->products()->delete();

        // 7. Delete wallets (after payment transactions)
        $merchant->wallets()->delete();

        // 8. Finally delete the merchant
        $merchant->delete();
    }

    /**
     * Delete user's direct relations
     */
    protected function deleteUserDirectRelations(User $user): void
    {
        // Delete any remaining account statements
        $user->accountStatements()->delete();

        // Delete any remaining payment transactions
        $user->paymentTransactions()->delete();

        // Delete any remaining account entries
        $user->accountEntries()->delete();

        // Delete any remaining orders
        $user->orders()->delete();
    }
}

