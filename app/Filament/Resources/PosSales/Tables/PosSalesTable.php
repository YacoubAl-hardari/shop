<?php

namespace App\Filament\Resources\PosSales\Tables;

use App\Enums\SalePaymentType;
use App\Models\PosSale;
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
                    ->formatStateUsing(fn(SalePaymentType $state): string => $state->displayLabel())
                    ->badge()
                    ->color(fn(SalePaymentType $state): string => match ($state) {
                        SalePaymentType::CASH => 'success',
                        SalePaymentType::CREDIT => 'danger',
                        SalePaymentType::PARTIAL => 'warning',
                    }),

                TextColumn::make('payment_method')
                    ->label('طريقة السداد')
                    ->formatStateUsing(fn($state, PosSale $record): string => $record->paymentMethodLabel() ?? '—')
                    ->placeholder('—'),

                TextColumn::make('total_amount')
                    ->label('إجمالي الفاتورة')

                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('المبلغ المدفوع')

                    ->toggleable(),

                TextColumn::make('credit_amount')
                    ->label('المبلغ المتبقي (الآجل)')

                    ->toggleable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn($state) => $state === 'completed' ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('payment_type')
                    ->label('نوع الدفع')
                    ->options(SalePaymentType::options()),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make(),
            ]);
    }
}
