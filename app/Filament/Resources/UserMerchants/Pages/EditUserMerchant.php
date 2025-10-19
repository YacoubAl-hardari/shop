<?php

namespace App\Filament\Resources\UserMerchants\Pages;

use App\Filament\Resources\UserMerchants\UserMerchantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserMerchant extends EditRecord
{
    protected static string $resource = UserMerchantResource::class;

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
