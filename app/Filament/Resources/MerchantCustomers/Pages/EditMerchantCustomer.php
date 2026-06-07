<?php

namespace App\Filament\Resources\MerchantCustomers\Pages;

use App\Filament\Resources\MerchantCustomers\MerchantCustomerResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMerchantCustomer extends EditRecord
{
    protected static string $resource = MerchantCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make()];
    }
}
