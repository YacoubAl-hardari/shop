<?php

namespace App\Filament\Resources\JournalEntries\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entry_number')
                    ->label('رقم القيد')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('entry_date')
                    ->label('التاريخ')
                    ->date()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->arabicLabel()),

                TextColumn::make('creator.name')
                    ->label('أنشئ بواسطة'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('entry_date', 'desc');
    }
}
