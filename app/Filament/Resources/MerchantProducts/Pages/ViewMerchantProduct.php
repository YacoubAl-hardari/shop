<?php

namespace App\Filament\Resources\MerchantProducts\Pages;

use App\Filament\Resources\MerchantProducts\MerchantProductResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMerchantProduct extends ViewRecord
{
    protected static string $resource = MerchantProductResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
