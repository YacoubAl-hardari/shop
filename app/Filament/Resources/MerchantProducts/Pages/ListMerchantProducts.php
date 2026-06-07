<?php

namespace App\Filament\Resources\MerchantProducts\Pages;

use App\Filament\Resources\MerchantProducts\MerchantProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMerchantProducts extends ListRecords
{
    protected static string $resource = MerchantProductResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('منتج جديد')];
    }
}
