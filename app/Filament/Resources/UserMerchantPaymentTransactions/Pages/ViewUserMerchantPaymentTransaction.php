<?php

namespace App\Filament\Resources\UserMerchantPaymentTransactions\Pages;

use App\Filament\Resources\UserMerchantPaymentTransactions\UserMerchantPaymentTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUserMerchantPaymentTransaction extends ViewRecord
{
    protected static string $resource = UserMerchantPaymentTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل'),
        ];
    }
}
