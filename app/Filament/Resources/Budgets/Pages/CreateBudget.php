<?php

namespace App\Filament\Resources\Budgets\Pages;

use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Budgets\BudgetResource;

class CreateBudget extends CreateRecord
{
    protected static string $resource = BudgetResource::class;

    public function getTitle(): string
    {
        return 'إنشاء ميزانية جديدة';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تم إنشاء الميزانية')
            ->body('تم إنشاء الميزانية بنجاح')
            ->send();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        
        // حساب المبلغ المتبقي
        $data['remaining_amount'] = $data['total_limit'] - ($data['spent_amount'] ?? 0);
        
        return $data;
    }
}

