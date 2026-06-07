<?php

namespace App\Filament\Resources\Distributors\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DistributorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('supplier.name')->label('المورد'),
                TextColumn::make('name')->label('الاسم')->searchable(),
                TextColumn::make('phone')->label('الهاتف'),
                IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()]);
    }
}
