<?php

namespace App\Filament\Resources\BudgetCategories\Pages;

use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\BudgetCategories\BudgetCategoryResource;

class CreateBudgetCategory extends CreateRecord
{
    protected static string $resource = BudgetCategoryResource::class;

    public function getTitle(): string
    {
        return 'إنشاء فئة جديدة';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تم إنشاء الفئة')
            ->body('تم إنشاء الفئة بنجاح')
            ->send();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        
        return $data;
    }
}

