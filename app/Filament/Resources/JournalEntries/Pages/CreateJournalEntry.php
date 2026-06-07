<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use App\Services\AccountingService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use InvalidArgumentException;

class CreateJournalEntry extends CreateRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['balance_check']);

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $team = Filament::getTenant();
        $lines = collect($data['lines'] ?? [])->map(fn ($line) => [
            'account_id' => $line['account_id'],
            'debit_amount' => $line['debit_amount'] ?? 0,
            'credit_amount' => $line['credit_amount'] ?? 0,
            'description' => $line['description'] ?? null,
        ])->all();

        try {
            return app(AccountingService::class)->post(
                $team,
                $lines,
                $data['description'],
                null,
                null,
                $data['entry_date'] ?? now(),
            );
        } catch (InvalidArgumentException $e) {
            Notification::make()
                ->title('خطأ في القيد')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
