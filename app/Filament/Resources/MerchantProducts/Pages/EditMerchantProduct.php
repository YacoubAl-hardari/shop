<?php

namespace App\Filament\Resources\MerchantProducts\Pages;

use App\Filament\Resources\MerchantProducts\MerchantProductResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMerchantProduct extends EditRecord
{
    protected static string $resource = MerchantProductResource::class;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make()];
    }
}
