<?php

namespace App\Filament\Resources\MerchantCustomers\Pages;

use App\Filament\Resources\MerchantCustomers\MerchantCustomerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMerchantCustomers extends ListRecords
{
    protected static string $resource = MerchantCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('عميل جديد')];
    }
}
