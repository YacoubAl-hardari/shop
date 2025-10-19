<?php

namespace App\Filament\Resources\Users\Pages;

use Illuminate\Support\Facades\DB;
use App\Models\UserWallet;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        DB::transaction(function () {
            // Create a wallet for the new user
            $this->createUserWallet();
        });
    }

    /**
     * Create a wallet for the new user
     */
    protected function createUserWallet(): void
    {
        // Check if user already has a wallet
        $hasExistingWallet = UserWallet::where('user_id', $this->record->id)
            ->exists();
        
        // Only create wallet if none exists
        if ($hasExistingWallet) {
            return;
        }

        // Create wallet with initial zero balance
        UserWallet::create([
            'user_id' => $this->record->id,
            'balance' => 0,
            'total_deposit' => 0,
            'total_withdraw' => 0,
            'total_transfer' => 0,
            'total_received' => 0,
            'total_sent' => 0,
        ]);
    }
}
