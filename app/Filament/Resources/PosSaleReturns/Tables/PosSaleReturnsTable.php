<?php

namespace App\Filament\Resources\PosSaleReturns\Tables;

use App\Enums\RefundMethod;
use App\Enums\ReturnType;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;

class PosSaleReturnsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('return_number')
                    ->label('رقم المرتجع')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->date('Y/m/d')
                    ->sortable(),

                TextColumn::make('originalSale.sale_number')
                    ->label('رقم الفاتورة الأصلية')
                    ->searchable()
                    ->url(fn($record) => $record->originalSale
                        ? route('filament.admin.resources.pos-sales.view', ['record' => $record->pos_sale_id, 'tenant' => $record->team->slug])
                        : null),

                BadgeColumn::make('return_type')
                    ->label('النوع')
                    ->formatStateUsing(fn(ReturnType $state) => $state->label())
                    ->color(fn(ReturnType $state) => $state === ReturnType::EXCHANGE ? 'warning' : 'danger'),

                TextColumn::make('returned_amount')
                    ->label('قيمة المُرجَع')
                    ->money('SAR'),

                TextColumn::make('exchange_amount')
                    ->label('قيمة البديل')
                    ->money('SAR')
                    ->toggleable(),

                TextColumn::make('price_difference')
                    ->label('فارق السعر')
                    ->money('SAR')
                    ->color(fn($record) => (float) $record->price_difference > 0
                        ? 'success'
                        : ((float) $record->price_difference < 0 ? 'danger' : 'gray'))
                    ->toggleable(),

                BadgeColumn::make('refund_method')
                    ->label('طريقة الرد')
                    ->formatStateUsing(fn($state) => $state instanceof RefundMethod ? $state->label() : $state)
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn($state) => $state === 'completed' ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('return_type')
                    ->label('النوع')
                    ->options(ReturnType::options()),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make(),
            ]);
    }
}
