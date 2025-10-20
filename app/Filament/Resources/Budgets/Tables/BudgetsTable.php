<?php

namespace App\Filament\Resources\Budgets\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class BudgetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم الميزانية')
                    ->searchable(),

                TextColumn::make('period')
                    ->label('الفترة')
                    ->formatStateUsing(fn ($state) => $state->getLabel()),

                TextColumn::make('total_limit')
                    ->label('حد الميزانية')
                    ->money('SAR'),

                TextColumn::make('spent_amount')
                    ->label('المصروف')
                    ->money('SAR'),

                TextColumn::make('remaining_amount')
                    ->label('المتبقي')
                    ->money('SAR'),

                IconColumn::make('is_active')
                    ->label('نشطة')
                    ->boolean(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}

