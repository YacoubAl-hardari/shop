<?php

namespace App\Filament\Resources\UserMerchantWallets\Pages;

use App\Filament\Resources\UserMerchantWallets\UserMerchantWalletResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUserMerchantWallet extends ViewRecord
{
    protected static string $resource = UserMerchantWalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل'),
        ];
    }
}
