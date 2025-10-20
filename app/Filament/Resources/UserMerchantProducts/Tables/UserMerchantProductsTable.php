<?php

namespace App\Filament\Resources\UserMerchantProducts\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UserMerchantProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('الصورة')
                    ->circular()
                    ->size(40),

                TextColumn::make('userMerchant.name')
                    ->label('التاجر')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('اسم المنتج')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('السعر')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('barcode')
                    ->label('الباركود')
                    ->searchable(),

                TextColumn::make('brand')
                    ->label('العلامة التجارية')
                    ->searchable(),

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
                    ->placeholder('جميع المنتجات')
                    ->trueLabel('المنتجات النشطة فقط')
                    ->falseLabel('المنتجات غير النشطة فقط'),
            ])
            ->recordActions([
                // Actions will be handled by the resource
            ])
            ->toolbarActions([
                // Bulk actions will be handled by the resource
            ]);
    }
}
