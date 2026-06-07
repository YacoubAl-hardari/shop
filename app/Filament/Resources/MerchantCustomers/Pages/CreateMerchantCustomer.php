<?php

namespace App\Filament\Resources\MerchantCustomers\Pages;

use App\Filament\Resources\MerchantCustomers\MerchantCustomerResource;
use App\Services\CustomerStatementShareService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMerchantCustomer extends CreateRecord
{
    protected static string $resource = MerchantCustomerResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        if (! $this->record->user_id) {
            return;
        }

        app(CustomerStatementShareService::class)->handleLinkedUserChange(
            $this->record->fresh(['team']),
            null,
            Auth::user(),
        );
    }
}
