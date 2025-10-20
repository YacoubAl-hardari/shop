<?php

namespace App\Filament\Resources\BudgetCategories\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Components\ColorEntry;
use Filament\Schemas\Components\IconEntry;
use Filament\Schemas\Components\Grid;

class BudgetCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('معلومات الفئة')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('name')
                            ->label('اسم الفئة'),

                        ColorEntry::make('color')
                            ->label('اللون'),

                        TextEntry::make('sort_order')
                            ->label('الترتيب'),
                    ]),

                    TextEntry::make('description')
                        ->label('الوصف'),

                    Grid::make(4)->schema([
                        TextEntry::make('budget_limit')
                            ->label('حد الميزانية')
                            ->money('SAR'),

                        TextEntry::make('spent_amount')
                            ->label('المصروف')
                            ->money('SAR'),

                        IconEntry::make('is_active')
                            ->label('نشطة')
                            ->boolean(),
                    ]),
                ]),
        ]);
    }
}

