<?php

namespace App\Filament\Resources\PosSaleReturns\Pages;

use App\Filament\Resources\PosSaleReturns\PosSaleReturnResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListPosSaleReturns extends ListRecords
{
    protected static string $resource = PosSaleReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('process_return')
                ->label('إرجاع / استبدال')
                ->icon(Heroicon::OutlinedArrowUturnLeft)
                ->url(static::getResource()::getUrl('create'))
                ->color('warning'),
        ];
    }
}
