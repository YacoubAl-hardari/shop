<?php

namespace App\Filament\Resources\UserMerchantAccountEntries\Pages;

use App\Filament\Resources\UserMerchantAccountEntries\UserMerchantAccountEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserMerchantAccountEntries extends ListRecords
{
    protected static string $resource = UserMerchantAccountEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()
            //     ->label('إضافة قيد جديد'),
        ];
    }
}
