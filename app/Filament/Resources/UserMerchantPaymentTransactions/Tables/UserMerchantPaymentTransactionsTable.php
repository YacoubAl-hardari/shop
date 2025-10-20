<?php

namespace App\Filament\Resources\UserMerchantPaymentTransactions\Tables;

use App\Enums\PaymentMethod;
use App\Enums\PaymentTransactionStatus;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class UserMerchantPaymentTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_number')
                    ->label('رقم المعاملة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('userMerchant.name')
                    ->label('التاجر')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('userMerchantWallet.account_name')
                    ->label('محفظة التاجر')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('USD')
                    ->sortable(),

                BadgeColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->formatStateUsing(fn ($state): string => $state instanceof PaymentMethod ? $state->getLabel() : $state)
                    ->colors([
                        'primary' => 'bank_transfer',
                        'success' => 'cash',
                        'warning' => 'check',
                        'info' => 'card',
                        'secondary' => 'wallet',
                    ]),

                BadgeColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn ($state): string => $state instanceof PaymentTransactionStatus ? $state->getLabel() : $state)
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'gray' => 'cancelled',
                    ]),

                TextColumn::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->date()
                    ->sortable(),

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
                SelectFilter::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('user_merchant_id')
                    ->label('التاجر')
                    ->relationship('userMerchant', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options(PaymentMethod::toArray()),

                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(PaymentTransactionStatus::toArray()),

                    DateRangeFilter::make('payment_date')
                    ->label('تاريخ الدفع'),
            ])
            ->recordActions([
                // Actions will be handled by the resource
            ])
            ->toolbarActions([
                // Bulk actions will be handled by the resource
            ]);
    }
}
