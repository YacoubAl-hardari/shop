<?php

namespace App\Filament\Resources\UserMerchantWallets\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UserMerchantWalletsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('userMerchant.name')
                    ->label('التاجر')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('account_name')
                    ->label('اسم الحساب')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('bank_account_number')
                    ->label('رقم الحساب المصرفي')
                    ->searchable(),

                TextColumn::make('bank_name')
                    ->label('اسم البنك')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_merchant_id')
                    ->label('التاجر')
                    ->relationship('userMerchant', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('جميع المحافظ')
                    ->trueLabel('المحافظ النشطة فقط')
                    ->falseLabel('المحافظ غير النشطة فقط'),
            ])
            ->actions([
                // Actions will be handled by the resource
            ])
            ->bulkActions([
                // Bulk actions will be handled by the resource
            ]);
    }
}
