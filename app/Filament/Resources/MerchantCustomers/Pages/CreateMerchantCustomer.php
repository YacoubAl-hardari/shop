<?php

namespace App\Filament\Resources\MerchantCustomers\Pages;

use App\Filament\Resources\MerchantCustomers\MerchantCustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMerchantCustomer extends CreateRecord
{
    protected static string $resource = MerchantCustomerResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
