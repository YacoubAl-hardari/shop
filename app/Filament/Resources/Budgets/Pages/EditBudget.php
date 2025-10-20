<?php

namespace App\Filament\Resources\Budgets\Pages;

use App\Filament\Resources\Budgets\BudgetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditBudget extends EditRecord
{
    protected static string $resource = BudgetResource::class;

    public function getTitle(): string
    {
        return 'تعديل الميزانية';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('عرض'),
            Actions\DeleteAction::make()
                ->label('حذف'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تم تحديث الميزانية')
            ->body('تم تحديث الميزانية بنجاح')
            ->send();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // حساب المبلغ المتبقي
        $data['remaining_amount'] = $data['total_limit'] - ($data['spent_amount'] ?? 0);
        
        return $data;
    }
}

