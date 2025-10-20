<?php

namespace App\Filament\Resources\Budgets\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Components\IconEntry;
use Filament\Schemas\Components\Grid;

class BudgetInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('معلومات الميزانية')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('name')
                            ->label('اسم الميزانية'),

                        TextEntry::make('period')
                            ->label('الفترة')
                            ->formatStateUsing(fn ($state) => $state->getLabel()),
                    ]),

                    TextEntry::make('description')
                        ->label('الوصف'),

                    Grid::make(4)->schema([
                        TextEntry::make('total_limit')
                            ->label('حد الميزانية')
                            ->money('SAR'),

                        TextEntry::make('spent_amount')
                            ->label('المصروف')
                            ->money('SAR'),

                        TextEntry::make('remaining_amount')
                            ->label('المتبقي')
                            ->money('SAR'),

                        IconEntry::make('is_active')
                            ->label('نشطة')
                            ->boolean(),
                    ]),
                ]),
        ]);
    }
}

