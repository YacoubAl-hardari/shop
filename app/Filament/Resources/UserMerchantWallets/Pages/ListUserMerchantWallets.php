<?php

namespace App\Filament\Resources\UserMerchantWallets\Pages;

use App\Filament\Resources\UserMerchantWallets\UserMerchantWalletResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserMerchantWallets extends ListRecords
{
    protected static string $resource = UserMerchantWalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة محفظة جديدة'),
        ];
    }
}
