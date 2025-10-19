<?php

namespace App\Filament\Resources\UserMerchantAccountStatements\Pages;

use App\Filament\Resources\UserMerchantAccountStatements\UserMerchantAccountStatementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserMerchantAccountStatement extends CreateRecord
{
    protected static string $resource = UserMerchantAccountStatementResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
