<?php

namespace App\Filament\Resources\UserMerchantAccountEntries\Pages;

use App\Filament\Resources\UserMerchantAccountEntries\UserMerchantAccountEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserMerchantAccountEntry extends EditRecord
{
    protected static string $resource = UserMerchantAccountEntryResource::class;

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
