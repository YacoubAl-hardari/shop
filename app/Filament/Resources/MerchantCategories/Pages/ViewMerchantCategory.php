<?php

namespace App\Filament\Resources\MerchantCategories\Pages;

use App\Filament\Resources\MerchantCategories\MerchantCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMerchantCategory extends ViewRecord
{
    protected static string $resource = MerchantCategoryResource::class;

    public function getTitle(): string
    {
        return 'عرض التصنيف';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل'),
            Actions\DeleteAction::make()
                ->label('حذف'),
        ];
    }
}
