<?php

namespace App\Filament\Resources\Suppliers\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الاسم')->searchable(),
                TextColumn::make('phone')->label('الهاتف'),
                TextColumn::make('balance')->label('الرصيد'),
                IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()]);
    }
}
