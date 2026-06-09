<?php

namespace App\Filament\Resources\PosSales\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;

class PosSalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sale_number')
                    ->label('رقم الفاتورة')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('created_at')
                    ->label('التاريخ والوقت')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),

                TextColumn::make('merchantCustomer.name')
                    ->label('العميل')
                    ->searchable()
                    ->default('عميل نقدي'),

                TextColumn::make('payment_type')
                    ->label('نوع الدفع')
                    ->badge()
                    ->color(fn ($state) => match ($state?->value) {
                        'cash' => 'success',
                        'credit' => 'danger',
                        'partial' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'cash' => 'نقدي',
                        'card' => 'شبكة (بطاقة)',
                        'bank_transfer' => 'تحويل بنكي',
                        default => $state ?? '—',
                    }),

                TextColumn::make('total_amount')
                    ->label('إجمالي الفاتورة')
                    ->money('SAR')
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('المبلغ المدفوع')
                    ->money('SAR')
                    ->toggleable(),

                TextColumn::make('credit_amount')
                    ->label('المبلغ المتبقي (الآجل)')
                    ->money('SAR')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn ($state) => $state === 'completed' ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('payment_type')
                    ->label('نوع الدفع')
                    ->options([
                        'cash' => 'نقدي',
                        'credit' => 'آجل بالكامل',
                        'partial' => 'دفع جزئي',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make(),
            ]);
    }
}
