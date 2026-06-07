<?php

namespace App\Filament\Resources\MerchantProducts\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MerchantProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الاسم')->searchable(),
                TextColumn::make('barcode')->label('الباركود')->searchable()->toggleable(),
                TextColumn::make('sku')->label('الرمز')->toggleable(),
                TextColumn::make('supplier.name')->label('المورد')->placeholder('—')->toggleable(),
                TextColumn::make('distributor.name')->label('الموزع')->placeholder('—')->toggleable(),
                TextColumn::make('price')->label('السعر')->money('SAR'),
                TextColumn::make('stock_quantity')->label('المخزون'),
                IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()]);
    }
}
