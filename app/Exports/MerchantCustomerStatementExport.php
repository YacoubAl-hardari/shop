<?php

namespace App\Exports;

use App\Models\MerchantCustomer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MerchantCustomerStatementExport implements WithMultipleSheets
{
    public function __construct(
        protected MerchantCustomer $customer,
        protected Collection $lines,
        protected ?string $merchantName = null,
        protected ?string $dateFrom = null,
        protected ?string $dateTo = null,
    ) {}

    public function sheets(): array
    {
        return [
            new MerchantCustomerStatementSummarySheet(
                $this->customer,
                $this->merchantName,
                $this->dateFrom,
                $this->dateTo,
            ),
            new MerchantCustomerStatementLinesSheet($this->lines),
        ];
    }
}
