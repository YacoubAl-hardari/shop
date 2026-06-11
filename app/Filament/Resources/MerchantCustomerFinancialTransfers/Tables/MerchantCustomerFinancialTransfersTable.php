<?php

namespace App\Filament\Resources\MerchantCustomerFinancialTransfers\Tables;

use App\Enums\CustomerFinancialTransferPurpose;
use App\Enums\CustomerFinancialTransferStatus;
use App\Filament\Resources\MerchantCustomerFinancialTransfers\MerchantCustomerFinancialTransferResource;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MerchantCustomerFinancialTransfersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('merchantCustomer.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('purpose')
                    ->label('الغرض')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof CustomerFinancialTransferPurpose
                        ? $state->getLabel()
                        : (string) $state),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cash' => 'نقد',
                        'card' => 'بطاقة',
                        'bank_transfer' => 'تحويل بنكي',
                        default => $state ?? '—',
                    }),
                TextColumn::make('paymentAccount.name')
                    ->label('حساب الاستلام')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn ($state): string => $state instanceof CustomerFinancialTransferStatus
                        ? $state->getColor()
                        : 'gray')
                    ->formatStateUsing(fn ($state): string => $state instanceof CustomerFinancialTransferStatus
                        ? $state->getLabel()
                        : (string) $state),
                TextColumn::make('submitter.name')
                    ->label('مقدّم الطلب')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('merchant_customer_id')
                    ->label('العميل')
                    ->relationship('merchantCustomer', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(CustomerFinancialTransferStatus::toArray()),
                SelectFilter::make('purpose')
                    ->label('الغرض')
                    ->options(CustomerFinancialTransferPurpose::toArray()),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('عرض')
                    ->url(fn ($record): string => MerchantCustomerFinancialTransferResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
