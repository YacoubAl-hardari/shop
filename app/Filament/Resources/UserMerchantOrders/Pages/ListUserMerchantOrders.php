<?php

namespace App\Filament\Resources\UserMerchantOrders\Pages;

use App\Filament\Resources\UserMerchantOrders\UserMerchantOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserMerchantOrders extends ListRecords
{
    protected static string $resource = UserMerchantOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة طلب جديد'),
        ];
    }
}
