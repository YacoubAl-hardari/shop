<?php

namespace App\Filament\Resources\UserMerchantProducts\Pages;

use App\Filament\Resources\UserMerchantProducts\UserMerchantProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserMerchantProduct extends EditRecord
{
    protected static string $resource = UserMerchantProductResource::class;

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
