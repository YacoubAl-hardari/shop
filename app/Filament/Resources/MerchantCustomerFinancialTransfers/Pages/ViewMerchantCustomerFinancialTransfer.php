<?php

namespace App\Filament\Resources\MerchantCustomerFinancialTransfers\Pages;

use App\Filament\Concerns\ReviewsCustomerFinancialTransfer;
use App\Filament\Resources\MerchantCustomerFinancialTransfers\MerchantCustomerFinancialTransferResource;
use Filament\Resources\Pages\ViewRecord;

class ViewMerchantCustomerFinancialTransfer extends ViewRecord
{
    use ReviewsCustomerFinancialTransfer;

    protected static string $resource = MerchantCustomerFinancialTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->makeApproveTransferAction(),
            $this->makeRejectTransferAction(),
        ];
    }
}
