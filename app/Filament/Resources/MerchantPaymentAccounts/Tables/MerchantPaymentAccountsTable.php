<?php

namespace App\Filament\Resources\MerchantPaymentAccounts\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MerchantPaymentAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('النوع')
                    ->formatStateUsing(fn ($state) => $state?->arabicLabel()),
                TextColumn::make('name')->label('الاسم')->searchable(),
                TextColumn::make('account_number')->label('رقم الحساب')->searchable(),
                IconColumn::make('is_default')->label('افتراضي')->boolean(),
                IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
