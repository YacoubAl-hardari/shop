<?php

namespace App\Filament\Resources\UserMerchantAccountEntries\Pages;

use App\Filament\Resources\UserMerchantAccountEntries\UserMerchantAccountEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserMerchantAccountEntry extends CreateRecord
{
    protected static string $resource = UserMerchantAccountEntryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
