<?php

namespace App\Filament\Resources\BudgetCategories\Pages;

use App\Filament\Resources\BudgetCategories\BudgetCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBudgetCategories extends ListRecords
{
    protected static string $resource = BudgetCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إنشاء فئة جديدة')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTitle(): string
    {
        return 'فئات الميزانية';
    }
}

