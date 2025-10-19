<?php

namespace App\Filament\Resources\UserMerchantWallets\Pages;

use App\Filament\Resources\UserMerchantWallets\UserMerchantWalletResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserMerchantWallet extends EditRecord
{
    protected static string $resource = UserMerchantWalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('عرض'),
            Actions\DeleteAction::make()
                ->label('حذف'),
        ];
    }
}
