<?php

namespace App\Filament\Resources\UserMerchantOrders\Pages;

use App\Filament\Resources\UserMerchantOrders\UserMerchantOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUserMerchantOrder extends ViewRecord
{
    protected static string $resource = UserMerchantOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل'),
        ];
    }
}
