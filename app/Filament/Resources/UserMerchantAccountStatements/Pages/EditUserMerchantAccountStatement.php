<?php

namespace App\Filament\Resources\UserMerchantAccountStatements\Pages;

use App\Filament\Resources\UserMerchantAccountStatements\UserMerchantAccountStatementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserMerchantAccountStatement extends EditRecord
{
    protected static string $resource = UserMerchantAccountStatementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\ViewAction::make()
            //     ->label('عرض'),
            // Actions\DeleteAction::make()
            //     ->label('حذف'),
        ];
    }
}
