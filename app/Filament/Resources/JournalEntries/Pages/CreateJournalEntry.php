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

        // ── التحقق من توازن القيد ──────────────────────────────────
        $lines  = collect($data['lines'] ?? []);
        $debit  = $lines->sum(fn ($l) => (float) ($l['debit_amount']  ?? 0));
        $credit = $lines->sum(fn ($l) => (float) ($l['credit_amount'] ?? 0));
        $diff   = round(abs($debit - $credit), 2);

        if ($diff !== 0.0) {
            Notification::make()
                ->title('⚖️ القيد غير متوازن — لا يمكن الحفظ')
                ->body(
                    'مجموع المدين: ' . number_format($debit,  2) . ' ر.س' . "\n" .
                    'مجموع الدائن: ' . number_format($credit, 2) . ' ر.س' . "\n" .
                    'الفرق: '        . number_format($diff,   2) . ' ر.س — يجب أن يكون صفراً'
                )
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }

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
