<?php

namespace App\Filament\Resources\MerchantCustomers\Pages;

use App\Filament\Concerns\RecordsCustomerPayment;
use App\Filament\Concerns\SharesCustomerStatement;
use App\Filament\Resources\MerchantCustomers\MerchantCustomerResource;
use App\Models\MerchantCustomer;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMerchantCustomer extends ViewRecord
{
    use RecordsCustomerPayment;
    use SharesCustomerStatement;

    protected static string $resource = MerchantCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->makeRecordCustomerPaymentAction(),
            $this->makeShareStatementAction(),
            $this->makeCloseStatementShareAction(),
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

    protected function getShareCustomer(): MerchantCustomer
    {
        return $this->record;
    }
}
