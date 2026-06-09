<?php

namespace App\Filament\Resources\PosSales\Pages;

use App\Filament\Resources\PosSales\PosSaleResource;
use App\Filament\Resources\PosSaleReturns\PosSaleReturnResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewPosSale extends ViewRecord
{
    protected static string $resource = PosSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('return_or_exchange')
                ->label('إرجاع / استبدال')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->url(fn () => route('filament.admin.resources.pos-sale-returns.create', [
                    'tenant' => $this->record->team->slug,
                    'sale_id' => $this->record->id,
                ])),
        ];
    }
}
