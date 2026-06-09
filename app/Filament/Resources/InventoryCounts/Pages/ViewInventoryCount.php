<?php

namespace App\Filament\Resources\InventoryCounts\Pages;

use App\Enums\InventoryCountStatus;
use App\Filament\Resources\InventoryCounts\InventoryCountResource;
use App\Models\InventoryCount;
use App\Models\InventoryCountItem;
use App\Services\InventoryCountService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ViewInventoryCount extends Page
{
    protected static string $resource = InventoryCountResource::class;
    protected string $view = 'filament.pages.view-inventory-count';

    public InventoryCount $record;
    public array $countedQuantities = [];

    public function mount(InventoryCount $record): void
    {
        $this->record = $record;
        $this->record->load('items.product', 'creator', 'approver');

        // تحميل الكميات الحالية
        foreach ($this->record->items as $item) {
            $this->countedQuantities[$item->id] = $item->counted_quantity;
        }
    }

    public function saveQuantity(int $itemId, $qty, ?string $notes = null): void
    {
        if (! $this->record->isEditable()) {
            Notification::make()->title('لا يمكن التعديل — الجرد مكتمل أو معتمد')->warning()->send();
            return;
        }

        $item = InventoryCountItem::find($itemId);
        if (! $item) return;

        $qtyVal = ($qty !== null && $qty !== '') ? (int) round((float) $qty) : 0;

        try {
            app(InventoryCountService::class)->updateCountedQuantity($item, $qtyVal, $notes);

            // تحديث الحالة إلى in_progress إذا كانت draft
            if ($this->record->status === InventoryCountStatus::DRAFT) {
                $this->record->update(['status' => InventoryCountStatus::IN_PROGRESS]);
                $this->record->refresh();
            }

            $this->countedQuantities[$itemId] = $qty;
            Notification::make()->title('تم الحفظ')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('خطأ')->body($e->getMessage())->danger()->send();
        }
    }

    public function completeCount(): void
    {
        try {
            app(InventoryCountService::class)->completeCount($this->record);
            $this->record->refresh();
            Notification::make()->title('تم إكمال الجرد — جاهز للاعتماد')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('خطأ')->body($e->getMessage())->danger()->send();
        }
    }

    public function approveCount(): void
    {
        $team = Filament::getTenant();
        try {
            app(InventoryCountService::class)->approveAndPost($this->record, $team);
            $this->record->refresh();
            Notification::make()
                ->title('تم اعتماد الجرد وترحيل القيود')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()->title('خطأ')->body($e->getMessage())->danger()->send();
        }
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        if ($this->record->isEditable()) {
            $actions[] = Action::make('complete')
                ->label('إكمال الجرد')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('info')
                ->requiresConfirmation()
                ->modalDescription('سيتم نقل الجرد لمرحلة "مكتمل". يمكنك بعدها الاعتماد وترحيل القيود.')
                ->action(fn () => $this->completeCount());
        }

        if ($this->record->canBeApproved()) {
            $actions[] = Action::make('approve')
                ->label('اعتماد وترحيل القيود')
                ->icon(Heroicon::OutlinedShieldCheck)
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('اعتماد الجرد السنوي')
                ->modalDescription('سيتم تحديث أرصدة المخزون وترحيل قيود فوارق الجرد. هذا الإجراء لا يمكن التراجع عنه.')
                ->action(fn () => $this->approveCount());
        }

        if ($this->record->status === InventoryCountStatus::APPROVED) {
            $this->record->load('journalEntry');
            if (!$this->record->journalEntry || $this->record->journalEntry->status === \App\Enums\JournalEntryStatus::VOID) {
                $actions[] = Action::make('repost_journal')
                    ->label('إعادة ترحيل القيد المحاسبي')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('إعادة ترحيل القيد المحاسبي للجرد')
                    ->modalDescription('القيد المحاسبي المرتبط بهذا الجرد ملغي أو غير موجود. هل تريد إعادة ترحيل قيد تسوية الجرد الآن؟')
                    ->action(fn () => $this->repostJournal());
            }
        }

        return $actions;
    }

    public function repostJournal(): void
    {
        $team = Filament::getTenant();
        try {
            app(InventoryCountService::class)->repostJournalEntry($this->record, $team);
            $this->record->refresh();
            Notification::make()
                ->title('تم إعادة ترحيل القيد المحاسبي بنجاح')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()->title('خطأ')->body($e->getMessage())->danger()->send();
        }
    }

    public function getTitle(): string
    {
        return 'جرد رقم '.$this->record->count_number.' — '.$this->record->fiscal_year;
    }
}
