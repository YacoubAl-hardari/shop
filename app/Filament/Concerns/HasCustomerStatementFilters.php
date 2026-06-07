<?php

namespace App\Filament\Concerns;

use App\Exports\MerchantCustomerStatementExport;
use App\Models\MerchantCustomer;
use App\Services\CustomerStatementQueryService;
use Filament\Actions\Action;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

trait HasCustomerStatementFilters
{
    public ?string $statementDateFrom = null;

    public ?string $statementDateTo = null;

    public string $statementSortDirection = 'desc';

    abstract protected function getStatementCustomer(): ?MerchantCustomer;

    abstract protected function getStatementTeamId(): ?int;

    abstract protected function getStatementMerchantName(): ?string;

    public function getStatementLines(): Collection
    {
        $customer = $this->getStatementCustomer();

        if ($customer === null) {
            return collect();
        }

        return app(CustomerStatementQueryService::class)->linesForCustomer(
            $customer,
            $this->getStatementTeamId(),
            $this->statementDateFrom,
            $this->statementDateTo,
            $this->statementSortDirection,
        );
    }

    public function resetStatementFilters(): void
    {
        $this->statementDateFrom = null;
        $this->statementDateTo = null;
        $this->statementSortDirection = 'desc';
    }

    protected function makeExportStatementAction(): Action
    {
        return Action::make('exportStatement')
            ->label('تصدير Excel')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->action(function () {
                $customer = $this->getStatementCustomer();

                if ($customer === null) {
                    return;
                }

                $lines = $this->getStatementLines();
                $slug = Str::slug($customer->name) ?: 'customer';
                $filename = 'customer_statement_'.$slug.'_'.now()->format('Y-m-d_His').'.xlsx';

                return Excel::download(
                    new MerchantCustomerStatementExport(
                        $customer,
                        $lines,
                        $this->getStatementMerchantName(),
                        $this->statementDateFrom,
                        $this->statementDateTo,
                    ),
                    $filename,
                );
            });
    }
}
