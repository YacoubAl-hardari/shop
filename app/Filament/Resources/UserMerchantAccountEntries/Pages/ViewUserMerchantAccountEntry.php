<?php

namespace App\Filament\Resources\UserMerchantAccountEntries\Pages;

use App\Filament\Resources\UserMerchantAccountEntries\UserMerchantAccountEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUserMerchantAccountEntry extends ViewRecord
{
    protected static string $resource = UserMerchantAccountEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make()
            //     ->label('تعديل'),
        ];
    }
}
