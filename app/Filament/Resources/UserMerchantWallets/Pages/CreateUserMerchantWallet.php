<?php

namespace App\Filament\Resources\UserMerchantWallets\Pages;

use App\Filament\Resources\UserMerchantWallets\UserMerchantWalletResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserMerchantWallet extends CreateRecord
{
    protected static string $resource = UserMerchantWalletResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
