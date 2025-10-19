<?php

namespace App\Filament\Resources\UserMerchantProducts\Pages;

use App\Filament\Resources\UserMerchantProducts\UserMerchantProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUserMerchantProduct extends ViewRecord
{
    protected static string $resource = UserMerchantProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل'),
        ];
    }
}
