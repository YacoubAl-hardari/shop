<?php

namespace App\Filament\Resources\Accounts\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('الرمز')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('اسم الحساب')
                    ->searchable(),

                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->arabicLabel()),

                TextColumn::make('normal_balance')
                    ->label('الرصيد الطبيعي')
                    ->formatStateUsing(fn ($state) => $state?->arabicLabel()),

                TextColumn::make('parent.name')
                    ->label('الحساب الأب'),

                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('code');
    }
}
