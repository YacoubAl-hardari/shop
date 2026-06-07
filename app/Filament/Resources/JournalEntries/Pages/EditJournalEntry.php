<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Enums\JournalEntryStatus;
use App\Filament\Resources\JournalEntries\JournalEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditJournalEntry extends EditRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->record->status !== JournalEntryStatus::DRAFT) {
            Notification::make()
                ->title('لا يمكن التعديل')
                ->body('لا يمكن تعديل قيد مرحّل أو ملغي')
                ->warning()
                ->send();

            redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
        }
    }
}
