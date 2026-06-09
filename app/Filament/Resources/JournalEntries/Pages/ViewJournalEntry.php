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
                ->modalHeading('إلغاء وتصفير القيد المحاسبي')
                ->modalDescription(fn () => $this->record->reference_type !== null 
                    ? 'تحذير: هذا القيد ناتج تلقائياً عن النظام (مبيعات/جرد/إغلاق). إلغاء هذا القيد محاسبياً سيقوم بإنشاء قيد عكسي لتصفير الأثر المالي، ولكنه لن يعدل المستند الأصلي أو كميات المخازن الميدانية. هل أنت متأكد من الاستمرار؟' 
                    : 'هل أنت متأكد من إلغاء وتصفير هذا القيد اليومي؟'
                )
                ->visible(fn () => $this->record->status->value === 'posted')
                ->action(function () {
                    try {
                        app(AccountingService::class)->voidEntry($this->record);

                        Notification::make()
                            ->title('تم إلغاء القيد')
                            ->success()
                            ->send();

                        $this->refreshFormData(['status', 'lines']);
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('خطأ أثناء إلغاء القيد')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('reverse_via_source')
                ->label('العملية العكسية (المستند الأصلي)')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('info')
                ->visible(fn () => $this->record->reference_type !== null)
                ->url(function () {
                    $refType = $this->record->reference_type;
                    $refId = $this->record->reference_id;
                    if (!$refType || !$refId) return null;

                    try {
                        switch ($refType) {
                            case \App\Models\PosSale::class:
                                return \App\Filament\Resources\PosSaleReturns\PosSaleReturnResource::getUrl('create') . '?sale_id=' . $refId;
                            case \App\Models\PosSaleReturn::class:
                                return \App\Filament\Resources\PosSaleReturns\PosSaleReturnResource::getUrl('view', ['record' => $refId]);
                            case \App\Models\InventoryCount::class:
                                return \App\Filament\Resources\InventoryCounts\InventoryCountResource::getUrl('view', ['record' => $refId]);
                            case \App\Models\FiscalYearClosing::class:
                                return \App\Filament\Pages\FiscalYearClosingPage::getUrl();
                        }
                    } catch (\Throwable $e) {
                        return null;
                    }
                    return null;
                }),
        ];
    }
}
