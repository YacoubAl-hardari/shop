<?php

namespace App\Filament\Resources\UserMerchantOrders\Pages;

use App\Exports\OrdersExport;
use App\Filament\Resources\UserMerchantOrders\UserMerchantOrderResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ViewUserMerchantOrder extends ViewRecord
{
    protected static string $resource = UserMerchantOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('تصدير Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $record = $this->record->load(['userMerchant', 'user', 'items']);
                    $slug = Str::slug($record->order_number ?? 'order') ?: 'order';
                    $filename = 'order_'.$slug.'_'.now()->format('Y-m-d_His').'.xlsx';

                    return Excel::download(
                        new OrdersExport(collect([$record])),
                        $filename,
                    );
                }),
            Actions\EditAction::make()
                ->label('تعديل'),
        ];
    }
}
