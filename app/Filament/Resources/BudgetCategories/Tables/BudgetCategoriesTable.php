<?php

namespace App\Filament\Resources\BudgetCategories\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;

class BudgetCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),

                ColorColumn::make('color')
                    ->label('اللون'),

                TextColumn::make('name')
                    ->label('اسم الفئة')
                    ->searchable(),

                TextColumn::make('budget_limit')
                    ->label('حد الميزانية')
                    ->money('SAR'),

                TextColumn::make('spent_amount')
                    ->label('المصروف')
                    ->money('SAR'),

                IconColumn::make('is_active')
                    ->label('نشطة')
                    ->boolean(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('sort_order');
    }
}

