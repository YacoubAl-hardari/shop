<?php

namespace App\Filament\Resources\InventoryCounts\Pages;

use App\Filament\Resources\InventoryCounts\InventoryCountResource;
use App\Services\InventoryCountService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListInventoryCounts extends ListRecords
{
    protected static string $resource = InventoryCountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('new_count')
                ->label('جرد جديد')
                ->icon(Heroicon::OutlinedPlusCircle)
                ->color('primary')
                ->form([
                    DatePicker::make('count_date')
                        ->label('تاريخ الجرد')
                        ->default(now())
                        ->required(),
                    TextInput::make('fiscal_year')
                        ->label('السنة المالية')
                        ->numeric()
                        ->default(now()->year)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $team = Filament::getTenant();
                    try {
                        $count = app(InventoryCountService::class)->createCount(
                            $team,
                            \Carbon\Carbon::parse($data['count_date']),
                            (int) $data['fiscal_year'],
                        );
                        Notification::make()
                            ->title('تم إنشاء جلسة الجرد')
                            ->body('رقم الجرد: '.$count->count_number.' — تم تحميل '.($count->items->count()).' منتج')
                            ->success()
                            ->send();

                        $this->redirect(InventoryCountResource::getUrl('view', ['record' => $count]));
                    } catch (\Throwable $e) {
                        Notification::make()->title('خطأ')->body($e->getMessage())->danger()->send();
                    }
                }),
        ];
    }
}
