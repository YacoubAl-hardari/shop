<?php

namespace App\Filament\Resources\UserMerchantAccountStatements\Pages;

use App\Filament\Resources\UserMerchantAccountStatements\UserMerchantAccountStatementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUserMerchantAccountStatement extends ViewRecord
{
    protected static string $resource = UserMerchantAccountStatementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make()
            //     ->label('تعديل'),
        ];
    }
}
