<?php

namespace App\Filament\Resources\MerchantCategories\Pages;

use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\MerchantCategories\MerchantCategoryResource;

class CreateMerchantCategory extends CreateRecord
{
    protected static string $resource = MerchantCategoryResource::class;

    public function getTitle(): string
    {
        return 'إنشاء تصنيف جديد';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تم إنشاء التصنيف')
            ->body('تم إنشاء التصنيف بنجاح')
            ->send();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Auth::user()?->current_team_id;
        
        return $data;
    }
}
