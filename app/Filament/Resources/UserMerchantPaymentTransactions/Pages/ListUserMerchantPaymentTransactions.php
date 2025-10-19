<?php

namespace App\Filament\Resources\UserMerchantPaymentTransactions\Pages;

use App\Filament\Resources\UserMerchantPaymentTransactions\UserMerchantPaymentTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserMerchantPaymentTransactions extends ListRecords
{
    protected static string $resource = UserMerchantPaymentTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة معاملة دفع جديدة'),
        ];
    }
}
