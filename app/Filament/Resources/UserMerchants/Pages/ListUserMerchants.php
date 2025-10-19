<?php

namespace App\Filament\Resources\UserMerchants\Pages;

use App\Filament\Resources\UserMerchants\UserMerchantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserMerchants extends ListRecords
{
    protected static string $resource = UserMerchantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة تاجر جديد'),
        ];
    }
}
