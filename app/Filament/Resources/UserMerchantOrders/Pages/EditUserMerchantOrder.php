<?php

namespace App\Filament\Resources\UserMerchantOrders\Pages;

use App\Filament\Resources\UserMerchantOrders\UserMerchantOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserMerchantOrder extends EditRecord
{
    protected static string $resource = UserMerchantOrderResource::class;

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
