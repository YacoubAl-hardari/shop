<?php

namespace App\Filament\Resources\UserMerchants\Pages;

use App\Filament\Resources\UserMerchants\UserMerchantResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUserMerchant extends ViewRecord
{
    protected static string $resource = UserMerchantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('financial_stats')
                ->label('الإحصائيات المالية')
                ->icon('heroicon-o-chart-bar')
                ->color('success')
                ->url(fn ($record) => static::getResource()::getUrl('financial-stats', ['record' => $record])),
            Actions\EditAction::make()
                ->label('تعديل'),
        ];
    }
}
