<?php

namespace App\Filament\Resources\BudgetCategories\Pages;

use App\Filament\Resources\BudgetCategories\BudgetCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditBudgetCategory extends EditRecord
{
    protected static string $resource = BudgetCategoryResource::class;

    public function getTitle(): string
    {
        return 'تعديل الفئة';
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
            ->title('تم تحديث الفئة')
            ->body('تم تحديث الفئة بنجاح')
            ->send();
    }
}

