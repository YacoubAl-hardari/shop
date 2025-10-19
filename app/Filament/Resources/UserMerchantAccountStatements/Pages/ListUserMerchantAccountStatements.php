<?php

namespace App\Filament\Resources\UserMerchantAccountStatements\Pages;

use App\Filament\Resources\UserMerchantAccountStatements\UserMerchantAccountStatementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserMerchantAccountStatements extends ListRecords
{
    protected static string $resource = UserMerchantAccountStatementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()
            //     ->label('إضافة كشف حساب جديد'),
        ];
    }
}
