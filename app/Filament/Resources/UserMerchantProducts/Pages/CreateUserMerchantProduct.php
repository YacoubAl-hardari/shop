<?php

namespace App\Filament\Resources\UserMerchantProducts\Pages;

use App\Filament\Resources\UserMerchantProducts\UserMerchantProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserMerchantProduct extends CreateRecord
{
    protected static string $resource = UserMerchantProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
