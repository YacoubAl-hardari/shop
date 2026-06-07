<?php

namespace App\Filament\Resources\MerchantCustomers\Pages;

use App\Filament\Concerns\HasCustomerStatementFilters;
use App\Filament\Concerns\RecordsCustomerPayment;
use App\Filament\Concerns\SharesCustomerStatement;
use App\Filament\Resources\MerchantCustomers\MerchantCustomerResource;
use App\Models\MerchantCustomer;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;

class MerchantCustomerStatement extends Page
{
    use HasCustomerStatementFilters;
    use RecordsCustomerPayment;
    use SharesCustomerStatement;

    protected static string $resource = MerchantCustomerResource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected string $view = 'filament.pages.merchant-customer-statement';

    public MerchantCustomer $record;

    public function mount(MerchantCustomer $record): void
    {
        $this->record = $record;
    }

    public function getTitle(): string
    {
        return 'كشف حساب: '.$this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->makeExportStatementAction(),
            $this->makeRecordCustomerPaymentAction(),
        ];
    }

    protected function getStatementCustomer(): ?MerchantCustomer
    {
        return $this->record;
    }

    protected function getStatementTeamId(): ?int
    {
        return Filament::getTenant()?->id;
    }

    protected function getStatementMerchantName(): ?string
    {
        return Filament::getTenant()?->name;
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
