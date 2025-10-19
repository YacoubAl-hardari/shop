<?php

namespace App\Filament\Resources\UserMerchants\Pages;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserMerchantAccountStatementRepository;
use App\Filament\Resources\UserMerchants\UserMerchantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserMerchant extends CreateRecord
{
    protected static string $resource = UserMerchantResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Get current user ID
        $data['user_id'] = Auth::user()->id;
        return $data;
    }

    protected function afterCreate(): void
    {
        DB::transaction(function () {
            // Create opening account statement for the merchant
            app(UserMerchantAccountStatementRepository::class)->createOpeningStatement($this->record);
        });
    }

}
