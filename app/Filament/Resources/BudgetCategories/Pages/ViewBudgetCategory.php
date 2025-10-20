<?php

namespace App\Filament\Resources\BudgetCategories\Pages;

use App\Filament\Resources\BudgetCategories\BudgetCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBudgetCategory extends ViewRecord
{
    protected static string $resource = BudgetCategoryResource::class;

    public function getTitle(): string
    {
        return 'عرض الفئة';
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

