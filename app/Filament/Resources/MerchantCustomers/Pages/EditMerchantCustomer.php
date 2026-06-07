<?php

namespace App\Filament\Resources\MerchantCustomers\Pages;

use App\Filament\Resources\MerchantCustomers\MerchantCustomerResource;
use App\Services\CustomerStatementShareService;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditMerchantCustomer extends EditRecord
{
    protected static string $resource = MerchantCustomerResource::class;

    protected ?int $previousUserId = null;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make()];
    }

    protected function beforeSave(): void
    {
        $this->previousUserId = $this->record->getOriginal('user_id');
    }

    protected function afterSave(): void
    {
        app(CustomerStatementShareService::class)->handleLinkedUserChange(
            $this->record->fresh(['team']),
            $this->previousUserId,
            Auth::user(),
        );
    }
}
