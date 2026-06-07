<?php

namespace App\Filament\Resources\MerchantCustomers\Pages;

use App\Filament\Concerns\RecordsCustomerPayment;
use App\Filament\Resources\MerchantCustomers\MerchantCustomerResource;
use App\Models\JournalLine;
use App\Models\MerchantCustomer;
use BackedEnum;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;

class MerchantCustomerStatement extends Page
{
    use RecordsCustomerPayment;

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
            $this->makeRecordCustomerPaymentAction(),
        ];
    }

    public function getStatementLines()
    {
        return JournalLine::query()
            ->where('subledger_type', MerchantCustomer::class)
            ->where('subledger_id', $this->record->id)
            ->with(['journalEntry', 'account'])
            ->orderByDesc('created_at')
            ->get();
    }

    protected function getPaymentCustomer(): MerchantCustomer
    {
        return $this->record;
    }
}
