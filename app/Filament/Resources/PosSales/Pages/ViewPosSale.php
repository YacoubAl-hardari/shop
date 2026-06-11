<?php

namespace App\Filament\Resources\PosSales\Pages;

use App\Filament\Resources\PosSales\PosSaleResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewPosSale extends ViewRecord
{
    protected static string $resource = PosSaleResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->record->load([
            'items.returnItems.saleReturn',
            'team',
            'paymentAccount',
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('return_or_exchange')
                ->label('إرجاع / استبدال')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->visible(fn (): bool => $this->record->hasReturnableItems())
                ->url(fn () => route('filament.admin.resources.pos-sale-returns.create', [
                    'tenant' => $this->record->team->slug,
                    'sale_id' => $this->record->id,
                ])),

            Action::make('fully_returned')
                ->label('تم إرجاع')
                ->icon('heroicon-o-check-circle')
                ->color('gray')
                ->disabled()
                ->visible(fn (): bool => $this->record->isFullyReturned()),
        ];
    }
}
