<?php

namespace App\Filament\Resources\MerchantCustomers\Pages;

use App\Filament\Concerns\RecordsCustomerPayment;
use App\Filament\Resources\MerchantCustomers\MerchantCustomerResource;
use App\Models\MerchantCustomer;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMerchantCustomer extends ViewRecord
{
    use RecordsCustomerPayment;

    protected static string $resource = MerchantCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->makeRecordCustomerPaymentAction(),
            Action::make('statement')
                ->label('كشف الحساب')
                ->url(fn () => MerchantCustomerResource::getUrl('statement', ['record' => $this->record])),
            EditAction::make(),
        ];
    }

    protected function getPaymentCustomer(): MerchantCustomer
    {
        return $this->record;
    }
}
