<?php

namespace App\Filament\Resources\UserMerchantProducts\Pages;

use App\Filament\Resources\UserMerchantProducts\UserMerchantProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserMerchantProducts extends ListRecords
{
    protected static string $resource = UserMerchantProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة منتج جديد'),
        ];
    }
}
