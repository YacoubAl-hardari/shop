<?php

namespace App\Filament\Resources\MerchantPaymentAccounts\Pages;

use App\Filament\Resources\MerchantPaymentAccounts\MerchantPaymentAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMerchantPaymentAccounts extends ListRecords
{
    protected static string $resource = MerchantPaymentAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('إضافة بنك أو بطاقة'),
        ];
    }
}
