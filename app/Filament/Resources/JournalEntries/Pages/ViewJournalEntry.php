<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use App\Services\AccountingService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewJournalEntry extends ViewRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('void')
                ->label('إلغاء القيد')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status->value === 'posted')
                ->action(function () {
                    app(AccountingService::class)->voidEntry($this->record);

                    Notification::make()
                        ->title('تم إلغاء القيد')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'lines']);
                }),
        ];
    }
}
