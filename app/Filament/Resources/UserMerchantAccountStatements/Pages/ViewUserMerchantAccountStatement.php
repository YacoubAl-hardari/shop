<?php

namespace App\Filament\Resources\UserMerchantAccountStatements\Pages;

use App\Exports\AccountStatementsExport;
use App\Filament\Resources\UserMerchantAccountStatements\UserMerchantAccountStatementResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ViewUserMerchantAccountStatement extends ViewRecord
{
    protected static string $resource = UserMerchantAccountStatementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('تصدير Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $record = $this->record->load(['userMerchant', 'user']);
                    $slug = Str::slug($record->userMerchant?->name ?? 'statement') ?: 'statement';
                    $filename = 'account_statement_'.$slug.'_'.now()->format('Y-m-d_His').'.xlsx';

                    return Excel::download(
                        new AccountStatementsExport(collect([$record])),
                        $filename,
                    );
                }),
        ];
    }
}
